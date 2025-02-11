<?php
$start_time = microtime(true);
/*
    TODO: 
    Implement this code in a laravel environment 
    Have user sign in and then we will save their video in our system
    Along with this we will save the 
    video path directory as data/videos/username/{videoname}/
    image path directory as data/images/username/{imagebame}/ 
    
    DB will need to save a entry for each video and image 
    this entry will contain the path for easy reference using SQL and PHP

    Images and Video Table will contain
    Date
    Pothole_Size
    Pothole_Severity
    UploadedBy
    UploadTime
    Pothole_Repair_Status
    ? Description
    ? Location Data i.e. Full Address and Lat/Long values
*/

// Define paths
$vid_upload_dir = "../public/qgis/data/videos";
$map_data_js_path = "../public/qgis/data/map_data_1.js";

// Check if a file has been uploaded
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['video_file'])) {
    $video_file = $_FILES['video_file'];

    // Ensure the file is an MP4
    // NEED TO IMPLEMENT THE LOGIC FOR A IMAGE HERE 
    if (strtolower(pathinfo($video_file['name'], PATHINFO_EXTENSION)) !== 'mp4') {
        echo 'Please upload a valid MP4 file.';
        exit;
    }

    // Create a unique directory for the video
    $video_name = pathinfo($video_file['name'], PATHINFO_FILENAME);
    $video_directory = $vid_upload_dir . $video_name;
    if (!is_dir($video_directory)) {
        mkdir($video_directory, 0777, true);
    }

    // Move uploaded file to the directory
    $video_path = $video_directory . '/' . $video_file['name'];
    if (!move_uploaded_file($video_file['tmp_name'], $video_path)) {
        echo 'Failed to upload the file.';
        exit;
    }

    echo 'File uploaded successfully. Processing metadata...<br>';

    // Run ExifTool to extract metadata
    $output = shell_exec("exiftool -ee " . escapeshellarg($video_path));
    file_put_contents("$video_directory/" . $video_name . "gps_output.txt", $output);

    // Initialize variables
    $first_point = null;
    $gps_data = [];
    $captured_sample_time = false;

    // Function to convert DMS to Decimal
    function dmsToDecimal($dms, $direction) {
        sscanf($dms, "%d deg %d' %f\"", $degrees, $minutes, $seconds);
        $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);
        return ($direction == 'S' || $direction == 'W') ? -$decimal : $decimal;
    }

    // Parse metadata
    $lines = explode("\n", $output);
    $current_sample_time = null;
    $thumbnail_counter = 0; // Initialize counter for thumbnails
    
    foreach ($lines as $line) {

        if (str_contains($line, "Create Date")){
               // Use a regular expression to capture the date
            if (preg_match('/Create Date\s+:\s+([\d: ]+)/', $line, $matches)) {
                $date = trim($matches[1]);
            }
        }

        if (str_contains($line, 'Sample Time')) {
            $current_sample_time = trim(explode(":", $line)[1]);
            $latitude = $longitude = $altitude = $speed = null; // Reset GPS data for the new sample
        }
    
        // Capture GPS data for the current sample time
        if ($current_sample_time) {
            if (str_contains($line, "GPS Latitude")) $latitude = trim(explode(":", $line)[1]);
            if (str_contains($line, "GPS Longitude")) $longitude = trim(explode(":", $line)[1]);
            // if (str_contains($line, "GPS Altitude") && !str_contains($line, "System")) $altitude = trim(explode(":", $line)[1]);
            // if (str_contains($line, "GPS Speed")) $speed = trim(explode(":", $line)[1]);
            // Parse GPS Altitude
            if (str_contains($line, "GPS Altitude")) {
                // Ignore "GPS Altitude System" lines
                if (str_contains($line, "System")) {
                    continue;
                }
                // Match numeric altitude values followed by "m"
                if (preg_match('/GPS Altitude\s*:\s*([\d.]+)\s*m/', $line, $matches)) {
                    $altitude = $matches[1]; // Extract the numeric altitude in meters
                }
            }


            // Parse GPS Speed
            if (str_contains($line, "GPS Speed") && !str_contains($line, "3D")) {
                if (preg_match('/GPS Speed\s*:\s*([\d.]+)/', $line, $matches)) {
                    $speed = $matches[1]; // Extract speed in m/s
                }
            }

            // If all required GPS data is captured, process the point
            if ($latitude && $longitude) {
                preg_match('/(\d+ deg \d+\' \d+\.\d+") ([NS])/', $latitude, $lat_matches);
                preg_match('/(\d+ deg \d+\' \d+\.\d+") ([EW])/', $longitude, $long_matches);
                $latitude_dec = dmsToDecimal($lat_matches[1], $lat_matches[2]);
                $longitude_dec = dmsToDecimal($long_matches[1], $long_matches[2]);

                // Generate the current thumbnail filename
                $thumbnail = $video_name . '_' . $thumbnail_counter . ".png";
                $thumbnail_counter++; // Increment the counter

                $current_point = [
                    "type" => "Feature",
                    "geometry" => [
                        "type" => "Point",
                        "coordinates" => [$longitude_dec, $latitude_dec]
                    ],
                    "properties" => [
                        "altitude" => $altitude ?? null,
                        "speed" => $speed ?? null,
                        "sampleTime" => $current_sample_time,
                        "thumbnail" => $thumbnail,
                        "date" => $date,
                        "size" => null, 
                        "severity" => null
                    ]
                ];
    
                // Save the first point with additional metadata
                if (!$first_point) {
                    $first_point = $current_point;
                    $first_point['properties']['videoPath'] = "data/videos/$video_name/{$video_file['name']}";
                    $first_point['properties']['dataFilePath'] = "data/videos/$video_name/$video_name" . "_data.json";
                }
    
                // Add the point to the GPS data and reset for the next sample time
                $gps_data[] = $current_point;
                $current_sample_time = null; // Mark sample as processed
            }
        }
    }
    

    // Save map_data.json for the video (entire GPS path)
    $video_geojson = [
        "type" => "FeatureCollection",
        "features" => $gps_data
    ];
        // Generate file path
    $file_path = "$video_directory/$video_name" . "_data.json";

    // Save GeoJSON data to file
    file_put_contents($file_path, json_encode($video_geojson, JSON_PRETTY_PRINT));


    // Append first point to map_data_1.js
    if ($first_point) {
        // Load existing data from map_data_1.js
        if (file_exists($map_data_js_path)) {
            $existing_data_raw = file_get_contents($map_data_js_path);

            // Strip the JavaScript variable declaration
            $existing_data_json = trim(str_replace('var json_map_data_1 =', '', $existing_data_raw));
            $existing_data_json = rtrim($existing_data_json, ';'); // Remove the trailing semicolon

            echo "Stripped JSON data: <pre>$existing_data_json</pre>"; // Debugging

            $existing_data = json_decode($existing_data_json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                echo "Error decoding JSON from map_data_1.js: " . json_last_error_msg();
                exit;
            }
        } else {
            $existing_data = ["features" => []];
        }

        // Append the new first point to the features array
        $existing_data["features"][] = $first_point;

        // Save the updated data back to map_data_1.js
        $updated_data = 'var json_map_data_1 = ' . json_encode($existing_data, JSON_PRETTY_PRINT) . ';';
        echo "Updated data to be written to map_data_1.js: <pre>$updated_data</pre>"; // Debugging

        if (file_put_contents($map_data_js_path, $updated_data) === false) {
            echo "Failed to write to map_data_1.js.";
            exit;
        }
    }



    echo "Metadata processed. Video data and map_data_1.js updated successfully.";
} else {
    echo 'No file uploaded.';
}

echo "\nCreating Video Thumbnails";

// Path to the ffmpeg binary
$ffmpeg_path = "../public/qgis/ffmpeg-7.1/ffmpeg";

// Construct the command with escaped variables
$thumbnail_command = escapeshellcmd($ffmpeg_path) . 
                     " -i " . escapeshellarg($video_path) . 
                     " -vf fps=1 " . escapeshellarg($video_directory . "/" . $video_name . '_' . "%d.png");

// Execute the command
$thumbnail_output = shell_exec($thumbnail_command);

// Check if the thumbnails were successfully generated
if ($thumbnail_output === null) {
    echo "Error generating video thumbnails.";
} else {
    echo "Video thumbnails created successfully.";
}

// End Clock Time in Seconds
$end_time = microtime(true);

// Calculate the Script Execution Time
$execution_time = ($end_time - $start_time);

echo "Script Execution Time = " . $execution_time . " sec";
?>

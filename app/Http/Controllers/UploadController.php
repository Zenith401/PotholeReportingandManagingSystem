<?php
/*
        Implement Queue, Worker, and Supervisor to do the heavy data processing work.
*/
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use App\Jobs;
use Faker\Provider\Medical;

class UploadController extends Controller
{
    public function upload()
    {
        return view('upload_video');
    }

    function uploadPost(Request $request){
        $file = $request->file("video_file");
        echo 'File Name: ' . $file->getClientOriginalName();

        echo '<br>';

        echo 'File Extension: ' . $file->getClientOriginalExtension();

        echo '<br>';

        echo 'File Real Path: ' . $file->getRealPath();
        echo '‹br>';

        echo 'File Size: ' . $file->getSize();
        echo '<br>';

        echo 'File Mime Type: ' . $file->getMimeType();

        $destinationPath = "uploads";

        if($file->move($destinationPath,$file->getClientOriginalName())){
            echo "File Uploaded Sucessfully";
        }
        else{
            echo "File Uploaded Failed";
        }

        ProcessVideosJob::dispatch();
    }


    function uploadPost1(Request $request)
    {
        // Validate uploaded file
        $request->validate([
            'video_file' => 'required|mimes:mp4|max:102400', // Maximum size: ~100MB
        ]);

        $user = auth()->user(); 
        $username = $user->username;

        // Define storage paths
        $videoDirectory = "data/videos/$username/";
        $imageDirectory = "data/images/$username/";

        // Save video file
        $file = $request->file('video_file');
        $videoName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $videoPath = $file->storeAs($videoDirectory, $file->getClientOriginalName(), 'public');

        // Run FFmpeg to generate thumbnails
        $this->generateThumbnails($videoPath, $videoName, $videoDirectory);

        // Process metadata using ExifTool
        $metadata = $this->extractMetadata($videoPath, $videoDirectory, $videoName);

        // Save metadata to the database
        $video = Media::create([
            'path' => $videoPath,
            'uploaded_by' => $user->id,
            'upload_time' => now(),
            'date' => $metadata['date'] ?? null,
            'description' => $metadata['description'] ?? null,
            'pothole_size' => null,
            'pothole_severity' => null,
            'pothole_repair_status' => 'Pending',
            'location_data' => json_encode($metadata['location']),
        ]);

        // Save thumbnails to the database
        foreach ($metadata['thumbnails'] as $thumbnail) {
            Media::create([
                'video_id' => $video->id,
                'path' => $thumbnail,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Video uploaded and processed successfully.',
            'video_id' => $video->id,
        ]);
    }

    private function generateThumbnails($videoPath, $videoName, $videoDirectory)
    {
        $ffmpegPath = base_path('public/qgis/ffmpeg-7.1/ffmpeg');
        $thumbnailDirectory = storage_path("app/public/$videoDirectory");
        $thumbnailCommand = escapeshellcmd($ffmpegPath) . " -i " . escapeshellarg($videoPath) . " -vf fps=1 " . escapeshellarg($thumbnailDirectory . '/' . $videoName . '_%d.png');
        shell_exec($thumbnailCommand);
    }

    private function extractMetadata($videoPath, $videoDirectory, $videoName)
    {
        $exifToolPath = 'exiftool'; // Ensure exiftool is installed and available
        $output = shell_exec("$exifToolPath -ee " . escapeshellarg(storage_path("app/public/$videoPath")));
        $metadataFile = storage_path("app/public/$videoDirectory/$videoName" . "_gps_output.txt");
        file_put_contents($metadataFile, $output);

        // Parse metadata here, similar to your original PHP script
        $locationData = [];
        $thumbnails = [];
        $date = null;

        $lines = explode("\n", $output);
        foreach ($lines as $line) {
            if (str_contains($line, 'GPS Latitude') || str_contains($line, 'GPS Longitude')) {
                // Parse and convert GPS data to decimal
                $locationData[] = $line; // Simplified for brevity
            }
            if (str_contains($line, 'Create Date')) {
                $date = trim(explode(':', $line)[1]);
            }
        }

        // Assuming thumbnails are generated successfully
        $thumbnailsPath = storage_path("app/public/$videoDirectory");
        $thumbnails = glob("$thumbnailsPath/$videoName*.png");

        return [
            'location' => $locationData,
            'thumbnails' => array_map(fn($path) => str_replace(storage_path("app/public/"), '', $path), $thumbnails),
            'date' => $date,
        ];
    }
}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Video</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .upload-container {
            background: #ffffff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 100%;
            max-width: 400px;
        }
        h1 {
            margin-bottom: 1.5rem;
            color: #333;
            font-size: 1.8rem;
        }
        label {
            display: block;
            margin-bottom: 1rem;
            font-weight: bold;
            color: #555;
        }
        input[type="file"] {
            display: block;
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        button {
            background: #007bff;
            color: #fff;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="upload-container">
        <h1>Upload Video</h1>
        <form action="{{route('upload.post')}}" method="POST" name="file" enctype="multipart/form-data">
            @csrf <!-- Include CSRF token for security -->
            <label for="video_file">Select MP4 Video:</label>
            <input type="file" name="video_file" id="video_file" accept=".mp4" required>
            <button type="submit">Upload</button>
        </form>
    </div>
</body>
</html>

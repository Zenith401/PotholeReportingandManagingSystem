<x-app-layout>
    <style>
        .image-box {
            background-image: url('/build/assets/home_background.jpg');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            justify-content: center; /* Centers cards horizontally */
            align-items: center;   /* Centers cards vertically */
        }

        h1 {
            font-size: 2rem;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
        }

        .card-container {
            display: flex;
            gap: 20px; /* Space between the cards */
        }

        .card {
            background-color: white;
            background-opacity: 0.9;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            padding: 20px;
            width: 200px;
            text-align: center;
        }

        .card h3 {
            font-size: 1.25rem;
            margin-bottom: 10px;
        }

        .card p {
            font-size: 1rem;
            color: #555;
        }
    </style>
    <!-- Wrapper for Background Image and Content -->
    <nav x-data>
        <div class="image-box">
            <!-- Card Container -->
            <div class="card-container">
                <!-- Card 1 -->
                <div class="card">
                    <h3>View Users</h3>
                    <p>Display .</p>
                </div>

                <!-- Card 2 -->
                <div class="card">
                    <h3>View Images</h3>
                    <p>View Images.</p>
                </div>

                <!-- Card 3 -->
                <div class="card">
                    <h3>Map View</h3>
                    <p>Map View.</p>
                </div>
            </div>
        </div>
    </nav>
</x-app-layout>

<?php
// Database Connection
$servername = "localhost";
$username = "root";  // replace with your database username
$password = "";      // replace with your database password
$dbname = "advertisement_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch images for each division
$leftImagesTop = [];
$leftImagesBottom = [];
$rightImages = [];

$result = $conn->query("SELECT image_path, division FROM images");
while ($row = $result->fetch_assoc()) {
    switch ($row['division']) {
        case 1:
            $leftImagesTop[] = $row['image_path'];
            break;
        case 2:
            $leftImagesBottom[] = $row['image_path'];
            break;
        case 3:
            $rightImages[] = $row['image_path'];
            break;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digiboard | Client</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            overflow: hidden; /* Prevent scrolling */
        }

        .left-division {
            text-align: center;
            color: white;
            height: 100vh; /* Full height of the viewport */
        }

        .top-division,
        .bottom-division,
        .right-division {
            border: 3px solid #0590fa; /* Dark blue border color */
            border-radius: 10px; /* Optional: round corners of the divisions */
            height: 50%; /* Occupy half of the left division */
            box-sizing: border-box; /* Include padding and border in the element's total width and height */
            overflow: hidden; /* Ensure that the image does not overflow */
            position: relative; /* For absolute positioning of text */
        }

        .top-division {
            margin-bottom: 10px; /* Space between top and bottom divisions */
        }

        .right-division {
            height: 100vh; /* Full height of the viewport */
            width: 50%; /* Right division takes half width */
            float: right; /* Right side alignment */
        }

        .top-division img,
        .bottom-division img,
        .right-division img {
            width: 100%;  /* Stretch to fill the width of the box */
            height: 100%; /* Stretch to fill the height of the box */
            object-fit: fill; /* Stretch the image to fill the box without maintaining aspect ratio */
            transition: opacity 0.5s ease;
            border-radius: 5px; /* Optional: round corners of the images */
            position: absolute; /* Allow overlap */
            top: 0;
            left: 0;
            opacity: 0; /* Start hidden */
        }

        .top-division img.visible,
        .bottom-division img.visible,
        .right-division img.visible {
            opacity: 1; /* Make visible */
        }

        .top-division h2,
        .top-division p {
            position: absolute;
            z-index: 1; /* Ensure text is on top of the image */
            color: white; /* Make sure text is readable */
            text-align: center; /* Center align text */
            width: 100%; /* Ensure text spans the width of the box */
        }

        .top-division {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(255, 255, 255, 0.1); /* Semi-transparent background */
        }

        .bottom-division {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(0, 0, 0, 0.7); /* Semi-transparent background */
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row vh-100">
            <!-- Left Division -->
            <div class="col-md-6 d-flex flex-column left-division">
                <div class="top-division flex-fill">
                    <div class="left-division">
                        <h2>Your Future Awaits!</h2>
                        <p>Discover innovative solutions and take your business to the next level.</p>
                        <?php if (!empty($leftImagesTop)): ?>
                            <img src="<?php echo $leftImagesTop[array_rand($leftImagesTop)]; ?>" class="img-fluid" alt="Top Advertisement">
                        <?php endif; ?>
                    </div>
                </div>
                <div class="bottom-division flex-fill">
                    <?php if (!empty($leftImagesBottom)): ?>
                        <img src="<?php echo $leftImagesBottom[array_rand($leftImagesBottom)]; ?>" class="img-fluid" alt="Bottom Advertisement">
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Division -->
            <div class="right-division" id="right-division">
                <?php if (!empty($rightImages)): ?>
                    <img src="<?php echo $rightImages[array_rand($rightImages)]; ?>" class="img-fluid" alt="Right Advertisement" style="opacity: 1;">
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            let leftImagesTop = <?php echo json_encode($leftImagesTop); ?>;
            let leftImagesBottom = <?php echo json_encode($leftImagesBottom); ?>;
            let rightImages = <?php echo json_encode($rightImages); ?>;

            let indexTop = 0;
            let indexBottom = 0;
            let indexRight = 0;

            // Function to change images for the top section
            function changeImageTop() {
                if (leftImagesTop.length === 0) return;

                $('.top-division img').removeClass('visible').fadeOut(300, function() {
                    indexTop = (indexTop + 1) % leftImagesTop.length; // Cycle through images
                    $(this).attr('src', leftImagesTop[indexTop]).fadeIn(300).addClass('visible');
                });
            }

            // Function to change images for the bottom section
            function changeImageBottom() {
                if (leftImagesBottom.length === 0) return;

                $('.bottom-division img').removeClass('visible').fadeOut(300, function() {
                    indexBottom = (indexBottom + 1) % leftImagesBottom.length; // Cycle through images
                    $(this).attr('src', leftImagesBottom[indexBottom]).fadeIn(300).addClass('visible');
                });
            }

            // Function to change images for the right section
            function changeImageRight() {
                if (rightImages.length === 0) return;

                $('#right-division img').removeClass('visible').fadeOut(300, function() {
                    indexRight = (indexRight + 1) % rightImages.length; // Cycle through images
                    $(this).attr('src', rightImages[indexRight]).fadeIn(300).addClass('visible');
                });
            }

            // Set intervals for changing images every 5 seconds
            setInterval(changeImageTop, 5000); 
            setInterval(changeImageBottom, 5000); 
            setInterval(changeImageRight, 10000);

            // Check isRefresh status every 30 minutes
            setInterval(() => {
                $.ajax({
                    url: "<?php echo $_SERVER['PHP_SELF']; ?>",
                    type: "GET",
                    data: { checkIsRefresh: true }, // Make a request to check the refresh status
                    success: function(data) {
                        if (data == '1') {
                            window.location.reload(); // Reload the page if isRefresh is 1
                        }
                    }
                });
            }, 1800000); // Check every 30 minutes (1800000 milliseconds)
        });
    </script>
</body>
</html>
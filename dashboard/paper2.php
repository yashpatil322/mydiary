<?php
// paper2.php (top section)
session_start();
require_once 'helpers/encryption.php';
require_once '../db.php';

$username = $_SESSION['username'] ?? null;
$entryId = $_GET['entry_id'] ?? null;

$images = [];

if ($username && $entryId) {
    $stmt = $conn->prepare("SELECT encrypted_image, encrypted_caption FROM entry_images WHERE username = ? AND entry_id = ?");
    $stmt->bind_param("si", $username, $entryId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($encrypted_image_blob, $encrypted_caption_blob);
        while ($stmt->fetch()) {
            // Convert the blobs to strings
            $encryptedImage = is_resource($encrypted_image_blob) ? stream_get_contents($encrypted_image_blob) : $encrypted_image_blob;
            $encryptedCaption = is_resource($encrypted_caption_blob) ? stream_get_contents($encrypted_caption_blob) : $encrypted_caption_blob;

            // Decrypt the data
            $decryptedImage = decryptData($encryptedImage);
            $decryptedCaption = decryptData($encryptedCaption);
            // Store in array
           $finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->buffer($decryptedImage);
$imageSrc = 'data:' . $mimeType . ';base64,' . base64_encode($decryptedImage);

$images[] = [
    'image' => $imageSrc,
    'caption' => $decryptedCaption
];
        }
    }
    $stmt->close();
} else {
    echo "<script>alert('Invalid access. Entry ID or user not found.');</script>";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Beautiful Journal Collage</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;700&family=Kalam:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Indie+Flower&family=Roboto&display=swap" rel="stylesheet">
    <style>
        /* General Styles */
        body {
            margin: 0;
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #a8ede6 0%, #fcd6ff 100%); /* Soft gradient */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden; /* Hide scrollbars if content overflows */
        }

        /* Phone Container Styles */
        .phone {
            width: 380px; /* Slightly wider */
            height: 650px; /* Slightly taller */
            margin: 20px auto;
            background: #222; /* Darker, sleek */
            border-radius: 40px; /* More pronounced rounded corners */
            padding: 15px; /* More padding */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), /* Stronger shadow */
                        0 0 0 5px rgba(255, 255, 255, 0.1); /* Subtle inner border/glow */
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden; /* Important for inner screen overflow */
        }

        .screen {
            width: calc(100% - 20px); /* Account for padding */
            height: calc(100% - 20px); /* Account for padding */
            background: #f8f8f8; /* Lighter, cleaner background */
            border-radius: 30px; /* Matches phone curvature */
            position: relative;
            overflow: hidden; /* Important for canvas */
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.1); /* Inner shadow for depth */
        }

        /* Controls */
        .controls {
            position: absolute;
            top: 15px; /* Adjust spacing */
            right: 15px; /* Adjust spacing */
            z-index: 100; /* Ensure on top */
            display: flex;
            gap: 8px; /* Space between buttons */
        }

        .btn {
            padding: 10px 15px; /* Larger hit area */
            background: linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%); /* Subtle gradient */
            border: 1px solid #ddd;
            border-radius: 25px; /* Pill-shaped buttons */
            cursor: pointer;
            font-weight: bold;
            color: #555;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn:hover {
            background: linear-gradient(135deg, #e0e0e0 0%, #ffffff 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .btn:active {
            transform: translateY(1px);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        /* Canvas */
        #canvas {
            display: block; /* Remove extra space below canvas */
            /* Canvas will be sized by JS */
        }

        /* Responsive adjustments */
        @media (max-width: 420px) { /* Adjust breakpoint for smaller phones */
            body {
                align-items: flex-start; /* Align to top on small screens */
                padding-top: 0;
            }
            .phone {
                width: 100vw;
                height: 100vh;
                margin: 0;
                border-radius: 0;
                padding: 0;
                box-shadow: none; /* No shadow on full screen */
            }
            .screen {
                border-radius: 0;
                width: 100%;
                height: 100%;
            }
            .controls {
                top: 10px;
                right: 10px;
                padding: 5px; /* Reduce padding on small screens */
            }
            .btn {
                padding: 6px 10px; /* Smaller buttons on small screens */
                font-size: 0.8em;
            }
        }
    </style>
</head>
<body>
    <div class="phone">
        <div class="screen">
            <div class="controls">
                <button class="btn" onclick="changeBg()">🎨 BG</button>
                <button class="btn" onclick="save()">💾 Save</button>
                <button class="btn"><a href="paper.php?entry_id=<?php echo $entryId; ?>">Go 🔙</a></button>
            </div>
            <canvas id="canvas"></canvas>
        </div>
    </div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>
<script>
    const canvas = new fabric.Canvas('canvas');

    function resizeCanvas() {
        const screen = document.querySelector('.screen');
        canvas.setWidth(screen.clientWidth);
        canvas.setHeight(screen.clientHeight);
        canvas.renderAll(); // Re-render after resize
    }
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas(); // Initial resize

    // More appealing backgrounds
    const backgrounds = [
        'linear-gradient(135deg, #a8ede6 0%, #fcd6ff 100%)', // Original soft
        'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)', // Warm pink/red
        'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)', // Cool blue
        'linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%)', // Fresh green/blue
        'linear-gradient(135deg, #ff9a9e 0%, #fad0c4 99%, #fad0c4 100%)', // Pastel pink/orange
        'url("polaroids/1.jpeg") center/cover',
        'url("polaroids/2.jpeg") center/cover',
        'url("polaroids/3.jpeg") center/cover',
        'url("polaroids/4.jpeg") center/cover',
        'url("polaroids/5.jpeg") center/cover',
        'url("polaroids/6.jpeg") center/cover',
        'url("polaroids/7.jpeg") center/cover',
        'url("polaroids/8.jpeg") center/cover',
        'url("polaroids/9.jpeg") center/cover',
        'url("polaroids/10.jpeg") center/cover',
        'url("polaroids/11.jpeg") center/cover',
        'url("polaroids/12.jpeg") center/cover',
        'url("polaroids/13.jpeg") center/cover',
        'url("polaroids/14.jpeg") center/cover',
        'url("polaroids/15.jpeg") center/cover',
        'url("polaroids/16.jpeg") center/cover',
        'url("polaroids/17.jpeg") center/cover',
        'url("polaroids/18.jpeg") center/cover',
        'url("polaroids/19.jpeg") center/cover',
        'url("polaroids/20.jpeg") center/cover',
        'url("polaroids/21.jpeg") center/cover',
        'url("polaroids/22.jpeg") center/cover',
        'url("polaroids/23.jpeg") center/cover',
        'url("polaroids/24.jpeg") center/cover',
        'url("polaroids/25.jpeg") center/cover',
        'url("polaroids/26.jpeg") center/cover',
        'url("polaroids/27.jpeg") center/cover',
        'url("polaroids/28.jpeg") center/cover',
        'url("polaroids/29.jpeg") center/cover',
        'url("polaroids/30.jpeg") center/cover',
        'url("polaroids/31.jpeg") center/cover',
        'url("polaroids/32.jpeg") center/cover',
        'url("polaroids/33.jpeg") center/cover',
        'url("polaroids/34.jpeg") center/cover',
        'url("polaroids/35.jpeg") center/cover',
        'url("polaroids/36.jpeg") center/cover',
        'url("polaroids/37.jpeg") center/cover',
        'url("polaroids/38.jpeg") center/cover',
        'url("polaroids/39.jpeg") center/cover',
        'url("polaroids/40.jpeg") center/cover',
        'url("polaroids/41.jpeg") center/cover',
        'url("polaroids/42.jpeg") center/cover',
        'url("polaroids/43.jpeg") center/cover',
        'url("polaroids/44.jpeg") center/cover',
        'url("polaroids/45.jpeg") center/cover',
        'url("polaroids/46.jpeg") center/cover',
        'url("polaroids/47.jpeg") center/cover',
        'url("polaroids/48.jpeg") center/cover',
        'url("polaroids/49.jpeg") center/cover',
        'url("polaroids/50.jpeg") center/cover',
        'url("polaroids/51.jpeg") center/cover',
        'url("polaroids/52.jpeg") center/cover',
        'url("polaroids/53.jpeg") center/cover',
        'url("polaroids/54.jpeg") center/cover',
        'url("polaroids/55.jpeg") center/cover',
        'url("polaroids/56.jpeg") center/cover',
        'url("polaroids/57.jpeg") center/cover',
        'url("polaroids/58.jpeg") center/cover',
        'url("polaroids/59.jpeg") center/cover',
        'url("polaroids/60.jpeg") center/cover',
        'url("polaroids/61.jpeg") center/cover',
        'url("polaroids/62.jpeg") center/cover',
        'url("polaroids/63.jpeg") center/cover',
        'url("polaroids/64.jpeg") center/cover',
        'url("polaroids/65.jpeg") center/cover',
        'url("polaroids/66.jpeg") center/cover',
        '#e0e0e0', // Light grey solid
        '#fff' // Pure white solid
    ];
    let bgIndex = 0;

    function changeBg() {
        bgIndex = (bgIndex + 1) % backgrounds.length;
        const currentBg = backgrounds[bgIndex];
        const screenElement = document.querySelector('.screen');

        // Clear any existing background styles
        screenElement.style.background = '';
        screenElement.style.backgroundColor = '';

        // Check if it's an image background (contains 'url(')
        if (currentBg.includes('url(')) {
            screenElement.style.background = currentBg;
            canvas.backgroundColor = null; // Make canvas transparent so image shows through
        }
        // Check if it's a gradient
        else if (currentBg.startsWith('linear-gradient')) {
            screenElement.style.background = currentBg;
            canvas.backgroundColor = null; // Make canvas transparent so gradient shows through
        }
        // It's a solid color
        else {
            screenElement.style.backgroundColor = currentBg;
            canvas.backgroundColor = currentBg;
        }

        canvas.renderAll();
    }

    const images = <?php echo json_encode($images); ?>;

    if (images && images.length > 0) {
        images.forEach((imgData, i) => {
            const imgElement = new Image();
            imgElement.src = imgData.image; // Use the data URI directly
            imgElement.crossOrigin = 'Anonymous'; // Important for canvas if images are from external sources, though here they are data URIs

            imgElement.onload = function() {
                let fabricImg = new fabric.Image(imgElement);

                // Calculate target width for image within the polaroid
                const targetImageWidth = 120; // Increased from 100 to 120
                fabricImg.scaleToWidth(targetImageWidth);

                // Ensure scaled height doesn't exceed a reasonable limit if aspect ratio is very tall
                const maxImageHeight = 140; // Increased from 120 to 140
                if (fabricImg.getScaledHeight() > maxImageHeight) {
                    fabricImg.scaleToHeight(maxImageHeight);
                }

                const framePadding = 10;
                const captionHeight = 18;
                const topPadding = 12;
                const bottomPadding = 3;

                // Polaroid frame dimensions with more compact caption area
                const frameWidth = fabricImg.getScaledWidth() + (framePadding * 2);
                const frameHeight = fabricImg.getScaledHeight() + framePadding + topPadding + captionHeight + bottomPadding;

                // Polaroid Frame
                const frame = new fabric.Rect({
                    width: frameWidth,
                    height: frameHeight,
                    fill: 'white',
                    stroke: '#eee', // Very subtle border
                    strokeWidth: 0.5,
                    shadow: 'rgba(0,0,0,0.2) 5px 5px 15px', // Subtle shadow for depth
                    rx: 3, // Slightly rounded corners for the frame
                    ry: 3
                });

                // Caption Text
                const captionText = new fabric.Text(imgData.caption || 'No Caption', {
                    fontSize: 14, // Reduced from 14 to 12
                    fontFamily: 'Indie Flower', // Use the loaded handwritten font
                    fill: '#333',
                    textAlign: 'center',
                    // Position relative to the frame with minimal space
                    left: framePadding, // Aligned with frame padding
                    top: topPadding + fabricImg.getScaledHeight() + framePadding + 1, // Reduced space below image from 3 to 1
                    width: fabricImg.getScaledWidth(), // Constrain text width to image width
                    breakWords: true, // Allow words to break
                    lineHeight: 1.0 // Tighter line height, reduced from 1.1 to 1.0
                });

                // Set image position relative to the frame with top padding
                fabricImg.set({
                    left: framePadding,
                    top: topPadding, // Add top padding
                });

                // Group together
                const polaroid = new fabric.Group([frame, fabricImg, captionText], {
                    left: Math.random() * (canvas.width - frameWidth - 20) + 10, // Ensure it's within bounds
                    top: Math.random() * (canvas.height - frameHeight - 20) + 10, // Ensure it's within bounds
                    angle: Math.random() * 30 - 15, // Rotate between -15 and 15 degrees
                    hasControls: true, // Allow user to move, scale, rotate
                    hasBorders: false, // No default fabric.js borders
                    padding: 5 // Padding for controls
                });

                // Center the text horizontally within the frame after group is formed
                // This will apply correctly when the group is added to canvas
                captionText.set({
                    left: frame.left + (frame.width / 2) - (captionText.width / 2),
                });


                canvas.add(polaroid);
                canvas.renderAll(); // Render after adding each polaroid

                // Ensure caption is centered after group is modified (moved, scaled, etc.)
                polaroid.on('modified', function() {
                    const currentFrame = polaroid._objects[0];
                    const currentFabricImg = polaroid._objects[1];
                    const currentText = polaroid._objects[2];

                    // Recalculate based on current scaled dimensions of the image and frame
                    const imgScaledHeight = currentFabricImg.getScaledHeight();
                    const frameScaledWidth = currentFrame.getScaledWidth();

                    currentText.set({
                        left: currentFrame.left + (frameScaledWidth / 2) - (currentText.width * currentText.scaleX / 2),
                        top: currentFabricImg.top + imgScaledHeight + framePadding + 1
                    });
                    canvas.renderAll();
                });
            };
            imgElement.onerror = function() {
                console.error("Error loading image:", imgData.image);
                const errorText = new fabric.Text('Image failed to load', {
                    fontSize: 12,
                    fill: 'red',
                    left: Math.random() * (canvas.width - 150),
                    top: Math.random() * (canvas.height - 150),
                    angle: Math.random() * 30 - 15
                });
                canvas.add(errorText);
                canvas.renderAll();
            };
        });
    } else {
        const noImg = new fabric.Text('No images found for this entry.', {
            left: canvas.width / 2,
            top: canvas.height / 2,
            originX: 'center',
            originY: 'center',
            fontSize: 20,
            fill: '#888'
        });
        canvas.add(noImg);
        canvas.renderAll();
    }

    function save() {
        const targetWidth = 4500;
        const targetHeight = 5500;

        const currentCanvasWidth = canvas.width;
        const currentCanvasHeight = canvas.height;

        const scaleX = targetWidth / currentCanvasWidth;
        const scaleY = targetHeight / currentCanvasHeight;
        const scale = Math.min(scaleX, scaleY); // Use the smaller scale to fit within bounds

        console.log(`Generating high-res image at scale: ${scale.toFixed(2)}x`);
        console.log(`Target resolution: ${Math.round(currentCanvasWidth * scale)}x${Math.round(currentCanvasHeight * scale)}`);

        const tempCanvas = document.createElement('canvas');
        const tempCtx = tempCanvas.getContext('2d');

        tempCanvas.width = Math.round(currentCanvasWidth * scale);
        tempCanvas.height = Math.round(currentCanvasHeight * scale);

        tempCtx.imageSmoothingEnabled = false; // Disable image smoothing for initial setup

        const currentBg = backgrounds[bgIndex];
        let backgroundRendered = false;

        const renderObjectsAndSave = () => {
            // Re-enable image smoothing for drawing objects like images and text for better quality
            tempCtx.imageSmoothingEnabled = true;
            tempCtx.imageSmoothingQuality = 'high';

            renderFabricObjectsNatively(tempCtx, scale, function() {
                saveUltraHighQualityImage(tempCanvas, `collage_${Date.now()}.png`); // Dynamic filename
            });
        };


        if (currentBg.includes('url(')) {
            const img = new Image();
            img.crossOrigin = 'anonymous';

            const urlMatch = currentBg.match(/url\(["']?([^"')]+)["']?\)/);
            if (urlMatch) {
                img.onload = function() {
                    // Draw background image to fit the tempCanvas
                    tempCtx.drawImage(img, 0, 0, tempCanvas.width, tempCanvas.height);
                    renderObjectsAndSave();
                };
                img.onerror = function() {
                    console.error('Failed to load background image for saving');
                    // Fallback to saving without background if image fails
                    saveWithoutBackground();
                };
                img.src = urlMatch[1];
            } else {
                saveWithoutBackground(); // Fallback if URL parsing fails
            }
        } else if (currentBg.startsWith('linear-gradient')) {
            const gradient = createCanvasGradient(tempCtx, currentBg, tempCanvas.width, tempCanvas.height);
            if (gradient) {
                tempCtx.fillStyle = gradient;
                tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
            }
            renderObjectsAndSave();
        } else {
            // For solid color backgrounds
            tempCtx.fillStyle = currentBg; // This color would be the body's background color
            tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
            renderObjectsAndSave();
        }
    }

    // Function to render fabric objects natively
    function renderFabricObjectsNatively(ctx, scale, callback) {
        try {
            const objects = canvas.getObjects();
            let processedObjects = 0;

            if (objects.length === 0) {
                callback();
                return;
            }

            // Apply global scale to the target context
            ctx.scale(scale, scale); // Apply scaling once for the whole context

            objects.forEach(obj => {
                ctx.save(); // Save context state before rendering each object

                // Fabric.js's render method draws the object relative to its own properties.
                // Since we've globally scaled the context, this will render it at high-res.
                obj.render(ctx);

                ctx.restore(); // Restore context state after rendering each object

                processedObjects++;
                if (processedObjects === objects.length) {
                    callback();
                }
            });

        } catch (error) {
            console.error('Error in renderFabricObjectsNatively:', error);
            alert('Error generating image: ' + error.message);
            // Consider calling a fallback save here if this error is critical
        }
    }


    // Ultra high quality save function
    function saveUltraHighQualityImage(canvasToSave, filename) {
        try {
            const dataURL = canvasToSave.toDataURL('image/png', 1.0);

            if (!dataURL || dataURL === 'data:,') {
                throw new Error('Failed to generate image data');
            }

            const link = document.createElement('a');
            link.download = filename;
            link.href = dataURL;
            link.setAttribute('type', 'image/png');

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            alert(`Native high-quality collage saved!\nResolution: ${canvasToSave.width}x${canvasToSave.height} pixels\nFile: ${filename}`);

        } catch (error) {
            console.error('Error saving image:', error);
            alert('Error saving image: ' + error.message);
        }
    }

    // Helper function to create canvas gradient from CSS gradient string (simplified)
    function createCanvasGradient(ctx, gradientString, width, height) {
        try {
            const match = gradientString.match(/linear-gradient\(([^)]+)\)/);
            if (!match) return null;

            const parts = match[1].split(',').map(s => s.trim());
            const angleDeg = parseFloat(parts[0]); // Extract angle as float
            const angleRad = (angleDeg - 90) * Math.PI / 180; // Convert to radians for canvas

            // Calculate start and end points of the gradient
            // This is a more accurate way to map CSS linear gradients to canvas gradients
            const len = Math.abs(width * Math.sin(angleRad)) + Math.abs(height * Math.cos(angleRad));
            const center_x = width / 2;
            const center_y = height / 2;

            const x1 = center_x - (Math.sin(angleRad) * len / 2);
            const y1 = center_y + (Math.cos(angleRad) * len / 2);
            const x2 = center_x + (Math.sin(angleRad) * len / 2);
            const y2 = center_y - (Math.cos(angleRad) * len / 2);

            const gradient = ctx.createLinearGradient(x1, y1, x2, y2);

            for (let i = 1; i < parts.length; i++) {
                const colorMatch = parts[i].match(/(#[a-fA-F0-9]{6}|#[a-fA-F0-9]{3}|rgba?\([^)]+\)|\w+)\s*(\d+%)?/);
                if (colorMatch) {
                    const color = colorMatch[1];
                    let position = (i - 1) / (parts.length - 1); // Distribute evenly if no explicit stops

                    if (colorMatch[2]) {
                        position = parseFloat(colorMatch[2]) / 100;
                    } else if (parts[i].includes('deg')) {
                         // Skip angle part if it was already extracted as first part
                         continue;
                    }
                    gradient.addColorStop(position, color);
                }
            }
            return gradient;
        } catch (e) {
            console.error('Error creating gradient:', e);
            return null;
        }
    }


    // Simplified fallback function using native rendering if background fails
    function saveWithoutBackground() {
        const targetWidth = 4500;
        const targetHeight = 5500;
        const currentCanvasWidth = canvas.width;
        const currentCanvasHeight = canvas.height;

        const scaleX = targetWidth / currentCanvasWidth;
        const scaleY = targetHeight / currentCanvasHeight;
        const scale = Math.min(scaleX, scaleY);

        const tempCanvas = document.createElement('canvas');
        const tempCtx = tempCanvas.getContext('2d');

        tempCanvas.width = Math.round(currentCanvasWidth * scale);
        tempCanvas.height = Math.round(currentCanvasHeight * scale);

        tempCtx.imageSmoothingEnabled = true; // Re-enable for objects
        tempCtx.imageSmoothingQuality = 'high';

        console.log(`Generating collage-only image at ${tempCanvas.width}x${tempCanvas.height}`);

        renderFabricObjectsNatively(tempCtx, scale, function() {
            saveUltraHighQualityImage(tempCanvas, `collage_only_${Date.now()}.png`); // Dynamic filename
        });
    }

    // Set initial background on body and canvas
    changeBg();
</script>
</body>
</html>
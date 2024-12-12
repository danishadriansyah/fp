<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Image</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        input[type="file"] {
            margin-bottom: 15px;
        }
        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .result {
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Upload Image for Detection</h2>
        <form id="uploadForm" enctype="multipart/form-data">
            <input type="file" id="image" name="image" accept="image/*" required>
            <br>
            <button type="submit">Upload & Detect</button>
        </form>
        <div class="result" id="result"></div>
    </div>

    <script>
        document.getElementById('uploadForm').addEventListener('submit', async function (event) {
            event.preventDefault();

            const formData = new FormData();
            const image = document.getElementById('image').files[0];

            if (!image) {
                alert('Please select an image.');
                return;
            }

            formData.append('image', image);

            try {
                const response = await fetch('/detect-image', {
                    method: 'POST',
                    body: formData,
                });

                const result = await response.json();

                if (result.success) {
                    document.getElementById('result').innerHTML = `
                        <p>Detection Successful!</p>
                        <pre>${JSON.stringify(result.data, null, 2)}</pre>
                    `;
                } else {
                    document.getElementById('result').innerHTML = `<p>Error: ${result.error}</p>`;
                }
            } catch (error) {
                document.getElementById('result').innerHTML = `<p>An error occurred: ${error.message}</p>`;
            }
        });
    </script>
</body>
</html>

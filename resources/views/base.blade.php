<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upload CSV File</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add this line to include Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <!-- Your existing scripts -->

    <style>
        body {
            margin: 0;
            font-family: "Lato", sans-serif;
            background-color: #f8f9fa;
        }

        .sidebar {
            margin: 0;
            padding: 0;
            width: 220px;
            background-color: #007bff;
            position: fixed;
            height: 100%;
            overflow: auto;
            color: white;
        }

        .sidebar h3 {
            text-align: center;
            padding: 1rem 0;
            margin-bottom: 1rem;
            color: white;
            border-bottom: 1px solid #0056b3;
            /* Divider color */
        }

        .sidebar a {
            display: block;
            padding: 0.75rem;
            text-decoration: none;
            color: white;
            position: relative;
            transition: background-color 0.3s;
        }

        .sidebar a:last-child {
            border-bottom: none;
            /* Remove divider from the last button */
        }

        .sidebar a.active,
        .sidebar a:hover {
            background-color: #0056b3;
        }

        .sidebar a.active:before {
            content: "";
            position: absolute;
            width: 3px;
            height: 100%;
            background-color: #ffffff;
            top: 0;
            left: 0;
        }

        div.content {
            margin-left: 220px;
            padding: 1rem;
            height: 1000px;
            background-color: #ffffff;
        }

        @media screen and (max-width: 700px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .sidebar a {
                text-align: center;
                float: none;
            }

            div.content {
                margin-left: 0;
            }
        }
    </style>


</head>

<body>

    <div class="sidebar">
        <div class="logo">
            <h3><i class="fas fa-file-csv"></i> CSV TOOL</h3>
        </div>
        <a class="" href="/"><i class="fas fa-cog"></i><span style="margin-left: 10px;">Process
                Csv</span></a>
        <a href="/clean/"><i class="fas fa-cogs"></i><span style="margin-left: 10px;">Clean File [ph,Em]</span></a>
        <a href="/auto-clean/"><i class="fas fa-envelope-open-text"></i><span style="margin-left: 10px;">Auto Clean
                [Email]</span></a>

        <a href="/editcsv/">Edit Csv</a>
        <a href="/prev/"><i class="fas fa-history"></i><span style="margin-left: 10px;">Prev Files</span></a>
    </div>

    @yield('content') <!-- This is where the unique content for each page will be inserted -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
</body>

</html>

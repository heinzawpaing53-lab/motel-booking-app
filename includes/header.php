<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo defined('PAGE_TITLE') ? PAGE_TITLE . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    luxury: {
                        100: '#F5F1E8',
                        200: '#E5D8C3',
                        300: '#B7A99A',
                        400: '#C8A96A',
                        500: '#A68B5B',
                        600: '#6D5848',
                        700: '#5A3A22',
                        800: '#3B2418',
                        900: '#2C1810',
                    },
                    success: '#2E8B57',
                    warning: '#D4A017',
                    error: '#8B2E2E',
                }
            }
        }
    }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css">
</head>
<body class="font-[Inter] bg-luxury-100 text-luxury-800">
<?php include_once 'navbar.php'; ?>

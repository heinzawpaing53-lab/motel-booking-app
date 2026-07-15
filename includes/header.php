<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?php echo defined('PAGE_TITLE') ? PAGE_TITLE . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    luxury: {
                        50: '#FAF7F2',
                        100: '#F5F1E8',
                        200: '#E5D8C3',
                        300: '#D2BA94',
                        400: '#C8A96A',
                        500: '#A87C4F',
                        600: '#7A5534',
                        700: '#5A3A22',
                        800: '#3B2418',
                        900: '#2C1810',
                    },
                    success: '#2E8B57',
                    warning: '#D4A017',
                    error: '#8B2E2E',
                },
                fontFamily: {
                    serif: ['"Cormorant Garamond"', '"Playfair Display"', 'Georgia', 'serif'],
                    sans: ['"Inter"', '"Manrope"', 'system-ui', 'sans-serif'],
                },
            }
        }
    }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css?v=<?php echo filemtime($_SERVER['DOCUMENT_ROOT'] . '/motel-app/assets/css/style.css'); ?>">
</head>
<body style="background-color: #2C1810; font-family: 'Inter', 'Manrope', system-ui, sans-serif; color: #3B2418; -webkit-font-smoothing: antialiased;" class="antialiased">
<?php include_once 'navbar.php'; ?>

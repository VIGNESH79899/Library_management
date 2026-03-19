<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(dirname(__DIR__)) . "/config/app.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/auth/login_user.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aurora Library | Student Portal</title>
    <script>
        (function () {
            try {
                var saved = localStorage.getItem('aurora_theme');
                var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (saved === 'dark' || (!saved && prefersDark)) {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/favicon.png">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f4effe',
                            100: '#e5d5fd',
                            200: '#d0b3fa',
                            300: '#b486f6',
                            400: '#9252f0',
                            500: '#7d2ae8',
                            600: '#5f2eea',
                            700: '#4e26c6',
                            800: '#3f1f9f',
                            900: '#311782',
                        }
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp 0.5s ease-out forwards',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        .glass-nav {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }
        .theme-card {
            background: #ffffff;
            border-color: #e2e8f0;
            color: #0f172a;
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }
        .theme-input {
            background: #ffffff;
            border-color: #e2e8f0;
            color: #0f172a;
        }
        .theme-soft {
            background: #f8fafc;
            color: #334155;
        }
        html.dark body {
            background: #0b1220 !important;
            color: #e2e8f0 !important;
        }
        html.dark .glass-nav {
            background: rgba(15, 23, 42, 0.88);
            border-bottom: 1px solid rgba(51, 65, 85, 0.9);
        }
        html.dark .theme-card,
        html.dark .bg-white,
        html.dark .bg-white\/90,
        html.dark .bg-white\/80,
        html.dark .bg-slate-50 {
            background-color: #111827 !important;
            color: #e2e8f0 !important;
        }
        html.dark .theme-input {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }
        html.dark .theme-soft {
            background: #1e293b !important;
            color: #cbd5e1 !important;
        }
        html.dark .border-slate-50,
        html.dark .border-slate-100,
        html.dark .border-slate-200,
        html.dark .border-slate-300,
        html.dark .border-gray-100 {
            border-color: #334155 !important;
        }
        html.dark .text-slate-900,
        html.dark .text-slate-800,
        html.dark .text-slate-700,
        html.dark .text-slate-600 {
            color: #e2e8f0 !important;
        }
        html.dark .text-slate-500,
        html.dark .text-slate-400,
        html.dark .text-slate-300 {
            color: #94a3b8 !important;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800">




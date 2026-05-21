<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedConnect — Votre santé, notre priorité</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        medical: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        teal: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            200: '#99f6e4',
                            300: '#5eead4',
                            400: '#2dd4bf',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#134e4a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .mobile-menu {
            transform: translateY(-100%);
            opacity: 0;
            transition: all 0.3s ease-in-out;
        }
        .mobile-menu.active {
            transform: translateY(0);
            opacity: 1;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans">

    <!-- Main Navigation -->
    <nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-lg border-b border-slate-200/80 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <!-- Logo -->
                <div class="flex items-center gap-2">
                    <a href="index.php" class="flex items-center gap-2 group">
                        <div class="w-10 h-10 bg-gradient-to-br from-medical-500 to-teal-500 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-105">
                            <i data-lucide="heart-pulse" class="w-6 h-6 text-white"></i>
                        </div>
                        <span class="text-xl font-extrabold tracking-tight text-slate-900 group-hover:text-medical-600 transition-colors">MedConnect</span>
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center gap-6">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Navigation selon le rôle -->
                        <?php if ($_SESSION['user_role'] === 'patient'): ?>
                            <a href="patient/dashboard.php" class="flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-medical-600 transition-colors">
                                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                Mon Espace
                            </a>
                            <a href="patient/prendre_rdv.php" class="flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-medical-600 transition-colors">
                                <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                                Prendre RDV
                            </a>
                            <a href="patient/historique.php" class="flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-medical-600 transition-colors">
                                <i data-lucide="history" class="w-4 h-4"></i>
                                Historique
                            </a>
                        <?php elseif ($_SESSION['user_role'] === 'medecin'): ?>
                            <a href="medecin/dashboard.php" class="flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-medical-600 transition-colors">
                                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                Dashboard
                            </a>
                            <a href="medecin/disponibilites.php" class="flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-medical-600 transition-colors">
                                <i data-lucide="calendar-clock" class="w-4 h-4"></i>
                                Disponibilités
                            </a>
                        <?php elseif ($_SESSION['user_role'] === 'admin'): ?>
                            <a href="admin/dashboard.php" class="flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-medical-600 transition-colors">
                                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                Dashboard
                            </a>
                            <a href="admin/medecin.php" class="flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-medical-600 transition-colors">
                                <i data-lucide="user-cog" class="w-4 h-4"></i>
                                Médecins
                            </a>
                        <?php endif; ?>

                        <!-- User Menu -->
                        <div class="flex items-center gap-3 pl-6 border-l border-slate-200">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-gradient-to-br from-medical-400 to-teal-400 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                    <?php echo strtoupper(substr($_SESSION['user_prenom'] ?? 'U', 0, 1)); ?>
                                </div>
                                <span class="text-sm font-medium text-slate-700">
                                    <?php echo htmlspecialchars($_SESSION['user_prenom'] ?? 'Utilisateur'); ?>
                                </span>
                            </div>
                            <a href="logout.php" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                                <span class="hidden lg:block">Déconnexion</span>
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-medical-600 transition-colors">
                            <i data-lucide="log-in" class="w-4 h-4"></i>
                            Connexion
                        </a>
                        <a href="register.php" class="flex items-center gap-2 px-5 py-2.5 text-sm font-semibold bg-gradient-to-r from-medical-500 to-teal-500 hover:from-medical-600 hover:to-teal-600 text-white rounded-xl shadow-lg shadow-medical-500/20 transition-all duration-300 hover:shadow-xl hover:scale-105">
                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                            Inscription
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-slate-100 transition-colors">
                    <i data-lucide="menu" class="w-6 h-6 text-slate-600"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="mobile-menu md:hidden bg-white border-t border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Mobile Navigation selon le rôle -->
                    <?php if ($_SESSION['user_role'] === 'patient'): ?>
                        <a href="patient/dashboard.php" class="flex items-center gap-3 px-4 py-3 text-slate-700 hover:bg-slate-50 rounded-lg transition-colors">
                            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                            Mon Espace
                        </a>
                        <a href="patient/prendre_rdv.php" class="flex items-center gap-3 px-4 py-3 text-slate-700 hover:bg-slate-50 rounded-lg transition-colors">
                            <i data-lucide="calendar-plus" class="w-5 h-5"></i>
                            Prendre RDV
                        </a>
                        <a href="patient/historique.php" class="flex items-center gap-3 px-4 py-3 text-slate-700 hover:bg-slate-50 rounded-lg transition-colors">
                            <i data-lucide="history" class="w-5 h-5"></i>
                            Historique
                        </a>
                    <?php elseif ($_SESSION['user_role'] === 'medecin'): ?>
                        <a href="medecin/dashboard.php" class="flex items-center gap-3 px-4 py-3 text-slate-700 hover:bg-slate-50 rounded-lg transition-colors">
                            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                            Dashboard
                        </a>
                        <a href="medecin/disponibilites.php" class="flex items-center gap-3 px-4 py-3 text-slate-700 hover:bg-slate-50 rounded-lg transition-colors">
                            <i data-lucide="calendar-clock" class="w-5 h-5"></i>
                            Disponibilités
                        </a>
                    <?php elseif ($_SESSION['user_role'] === 'admin'): ?>
                        <a href="admin/dashboard.php" class="flex items-center gap-3 px-4 py-3 text-slate-700 hover:bg-slate-50 rounded-lg transition-colors">
                            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                            Dashboard
                        </a>
                        <a href="admin/medecin.php" class="flex items-center gap-3 px-4 py-3 text-slate-700 hover:bg-slate-50 rounded-lg transition-colors">
                            <i data-lucide="user-cog" class="w-5 h-5"></i>
                            Médecins
                        </a>
                    <?php endif; ?>

                    <div class="border-t border-slate-200 my-3"></div>

                    <div class="flex items-center gap-3 px-4 py-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-medical-400 to-teal-400 rounded-full flex items-center justify-center text-white font-semibold">
                            <?php echo strtoupper(substr($_SESSION['user_prenom'] ?? 'U', 0, 1)); ?>
                        </div>
                        <div>
                            <p class="font-medium text-slate-800"><?php echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?></p>
                            <p class="text-xs text-slate-500 capitalize"><?php echo $_SESSION['user_role']; ?></p>
                        </div>
                    </div>

                    <a href="logout.php" class="flex items-center gap-3 px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg transition-colors mt-2">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                        Déconnexion
                    </a>
                <?php else: ?>
                    <a href="login.php" class="flex items-center gap-3 px-4 py-3 text-slate-700 hover:bg-slate-50 rounded-lg transition-colors">
                        <i data-lucide="log-in" class="w-5 h-5"></i>
                        Connexion
                    </a>
                    <a href="register.php" class="flex items-center gap-3 px-4 py-3 text-medical-600 hover:bg-medical-50 rounded-lg transition-colors">
                        <i data-lucide="user-plus" class="w-5 h-5"></i>
                        Inscription
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');

            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', () => {
                    mobileMenu.classList.toggle('active');
                    const icon = mobileMenuBtn.querySelector('i');
                    if (mobileMenu.classList.contains('active')) {
                        icon.setAttribute('data-lucide', 'x');
                    } else {
                        icon.setAttribute('data-lucide', 'menu');
                    }
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                });
            }

            // Initialize Lucide icons only if not already initialized
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>

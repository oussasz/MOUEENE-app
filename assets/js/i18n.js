/**
 * MOUEENE - Client-side i18n (EN/FR/AR)
 *
 * Goals:
 * - Apply language to every page without server-side rendering
 * - Translate visible text nodes + key attributes (placeholder/title/aria-label/alt/value)
 * - Handle dynamic DOM updates via MutationObserver
 * - Persist language in localStorage
 * - Arabic RTL support + smooth transition
 */

(function () {
  "use strict";

  const STORAGE_KEY = "moueene_lang";
  const SUPPORTED = ["en", "fr", "ar"];

  // Phrase-level translations (best quality)
  const PHRASES = {
    fr: {
      // Brand
      Moueene: "Moueene",
      MOUEENE: "MOUEENE",
      "Moueene - Home Services Platform":
        "Moueene - Plateforme de services à domicile",
      "Moueene - Connect with trusted service providers for all your needs":
        "Moueene - Connectez-vous avec des prestataires de confiance pour tous vos besoins",

      // Navigation
      Home: "Accueil",
      Services: "Services",
      Providers: "Prestataires",
      "How It Works": "Comment ça marche",
      "About Us": "À propos",
      Contact: "Contact",
      Login: "Connexion",
      "Sign Up": "Inscription",
      "Sign Up Now": "Inscrivez-vous maintenant",
      "Book Now": "Réserver",
      "Become a Service Provider": "Devenir prestataire",

      // Hero Section
      "WELCOME TO MOUEENE": "BIENVENUE SUR MOUEENE",
      "Your one-stop solution for all home service needs, connecting you with trusted professionals for cleaning, gardening, nursing, and more.":
        "Votre solution unique pour tous vos besoins de services à domicile, vous connectant avec des professionnels de confiance pour le nettoyage, le jardinage, les soins infirmiers et plus encore.",
      "What service do you need today?":
        "De quel service avez-vous besoin aujourd'hui ?",

      // Categories
      "Discover Popular Categories": "Découvrez les catégories populaires",
      "Browse through our most requested service categories":
        "Parcourez nos catégories de services les plus demandées",
      Childcare: "Garde d'enfants",
      Tutoring: "Cours particuliers",
      Cleaning: "Nettoyage",
      Gardening: "Jardinage",
      "Pet Care": "Soins pour animaux",
      "Elderly Care": "Soins aux personnes âgées",
      Repairs: "Réparations",
      "Home Services": "Services à domicile",

      // How It Works
      "Getting started with Moueene is easy":
        "Commencer avec Moueene est facile",
      "Choose Service": "Choisir un service",
      "Browse through our categories and find the service you need":
        "Parcourez nos catégories et trouvez le service dont vous avez besoin",
      "Find Provider": "Trouver un prestataire",
      "Compare profiles, reviews, and rates to find your perfect match":
        "Comparez les profils, les avis et les tarifs pour trouver votre partenaire idéal",
      Book: "Réserver",
      "Schedule your service at a time that works best for you":
        "Planifiez votre service au moment qui vous convient le mieux",
      Evaluation: "Évaluation",
      "Rate your experience and help others find great service":
        "Évaluez votre expérience et aidez les autres à trouver un excellent service",

      // Featured Services
      "Top Services": "Meilleurs services",
      "Explore our most booked services right now":
        "Explorez nos services les plus réservés en ce moment",
      "Browse All Services": "Parcourir tous les services",
      "Quick View": "Aperçu rapide",
      "Loading top services...": "Chargement des meilleurs services...",
      "No services available right now.":
        "Aucun service disponible pour le moment.",
      "Failed to load top services":
        "Échec du chargement des meilleurs services",

      // CTA Section
      "Ready to Get Started?": "Prêt à commencer ?",
      "Join thousands of satisfied customers and trusted service providers on Moueene":
        "Rejoignez des milliers de clients satisfaits et de prestataires de confiance sur Moueene",
      "Contact Us": "Contactez-nous",

      // Footer
      "Quick Links": "Liens rapides",
      "Find Providers": "Trouver des prestataires",
      Support: "Assistance",
      FAQ: "FAQ",
      "Privacy Policy": "Politique de confidentialité",
      "Terms of Service": "Conditions d'utilisation",
      "Help Center": "Centre d'aide",
      "All rights reserved.": "Tous droits réservés.",
      "© 2026 Moueene. All rights reserved.":
        "© 2026 Moueene. Tous droits réservés.",
      "2026 Moueene": "2026 Moueene",
      "Moueene connects customers with trusted service providers for all their home and personal needs.":
        "Moueene connecte les clients avec des prestataires de confiance pour tous leurs besoins domestiques et personnels.",

      // Auth Pages
      "Welcome Back": "Bon retour",
      "Welcome back": "Bon retour",
      "Login to manage your account": "Connectez-vous pour gérer votre compte",
      Customer: "Client",
      Provider: "Prestataire",
      "Admin Portal": "Portail Admin",
      "Sign in to manage the platform":
        "Connectez-vous pour gérer la plateforme",
      "Email Address": "Adresse e-mail",
      Email: "E-mail",
      Password: "Mot de passe",
      "Enter your password": "Entrez votre mot de passe",
      "Sign In": "Se connecter",
      "Signing in...": "Connexion...",
      "Back to main site": "Retour au site",
      "Invalid credentials": "Identifiants invalides",
      "Connection error. Please try again.":
        "Erreur de connexion. Veuillez réessayer.",
      "Remember me": "Se souvenir de moi",
      "Forgot Password?": "Mot de passe oublié ?",
      "Or continue with": "Ou continuer avec",
      "Don't have an account?": "Vous n'avez pas de compte ?",
      "Already have an account?": "Vous avez déjà un compte ?",
      "Connecting you with trusted professionals for all your home service needs.":
        "Vous connecter avec des professionnels de confiance pour tous vos besoins de services à domicile.",

      // Registration
      "Create Account": "Créer un compte",
      "Join Moueene today": "Rejoignez Moueene aujourd'hui",
      "First Name": "Prénom",
      "Last Name": "Nom",
      "Phone Number": "Numéro de téléphone",
      "Confirm Password": "Confirmer le mot de passe",
      "I agree to the": "J'accepte les",
      "Terms and Conditions": "Conditions générales",
      and: "et",
      "Create your account": "Créer votre compte",
      "hello@example.com": "bonjour@exemple.com",

      // Dashboard
      "Loading bookings...": "Chargement des réservations...",
      "No active bookings found.": "Aucune réservation active trouvée.",
      Dashboard: "Tableau de bord",
      Bookings: "Réservations",
      Messages: "Messages",
      Profile: "Profil",
      Settings: "Paramètres",
      Logout: "Déconnexion",
      Search: "Rechercher",
      User: "Utilisateur",
      with: "avec",
      Pending: "En attente",
      Confirmed: "Confirmé",
      Completed: "Terminé",
      Cancelled: "Annulé",
      "Active Bookings": "Réservations actives",
      "Completed Bookings": "Réservations terminées",
      "My Bookings": "Mes réservations",
      Favorites: "Favoris",
      Notifications: "Notifications",
      "View All": "Voir tout",

      // Validation
      "This field is required": "Ce champ est obligatoire",
      "Please enter a valid email address":
        "Veuillez saisir une adresse e-mail valide",
      "Passwords do not match": "Les mots de passe ne correspondent pas",
      "Registration failed. Please try again.":
        "L'inscription a échoué. Veuillez réessayer.",
      "Login failed. Please try again.":
        "La connexion a échoué. Veuillez réessayer.",
      "Failed to update profile.": "Échec de la mise à jour du profil.",
      "Failed to change password.": "Échec du changement de mot de passe.",

      // Access
      "Access denied": "Accès refusé",
      "You do not have permission to view this page.":
        "Vous n'avez pas la permission de voir cette page.",
      Message: "Message",
      OK: "OK",

      // Provider Status
      Account: "Compte",
      "Account Suspended": "Compte suspendu",
      "Account Deactivated": "Compte désactivé",
      "Verified Provider": "Prestataire vérifié",
      "Verification Rejected": "Vérification refusée",
      "Pending Verification": "Vérification en attente",

      // Services Page
      "All Services": "Tous les services",
      "Find the perfect service for your needs":
        "Trouvez le service parfait pour vos besoins",
      Filter: "Filtrer",
      Filters: "Filtres",
      Category: "Catégorie",
      Categories: "Catégories",
      "All Categories": "Toutes les catégories",
      "Price Range": "Fourchette de prix",
      Min: "Min",
      Max: "Max",
      Rating: "Note",
      "Apply Filters": "Appliquer les filtres",
      "Clear Filters": "Effacer les filtres",
      "Sort by": "Trier par",
      "Most Popular": "Plus populaires",
      Newest: "Plus récents",
      "Price: Low to High": "Prix : croissant",
      "Price: High to Low": "Prix : décroissant",
      "Highest Rated": "Mieux notés",
      reviews: "avis",
      "No services found": "Aucun service trouvé",
      "per hour": "par heure",
      "/hr": "/h",
      "/hour": "/heure",
      "Starting from": "À partir de",
      "Book Service": "Réserver le service",
      "View Details": "Voir les détails",

      // About Page
      "About Moueene": "À propos de Moueene",
      "Our Story": "Notre histoire",
      "Our Mission": "Notre mission",
      "Our Vision": "Notre vision",
      "Our Values": "Nos valeurs",
      "Meet Our Team": "Rencontrez notre équipe",
      Trust: "Confiance",
      Quality: "Qualité",
      Innovation: "Innovation",
      Community: "Communauté",

      // Contact Page
      "Get in Touch": "Contactez-nous",
      "Send us a message": "Envoyez-nous un message",
      "Your Name": "Votre nom",
      "Your Email": "Votre e-mail",
      Subject: "Sujet",
      "Your Message": "Votre message",
      "Send Message": "Envoyer le message",
      Address: "Adresse",
      Phone: "Téléphone",

      // Admin
      "Admin Dashboard": "Tableau de bord Admin",
      "Admin Panel": "Panneau Admin",
      "Admin Dashboard - Moueene": "Tableau de bord Admin - Moueene",
      "Admin Login - Moueene": "Connexion Admin - Moueene",
      "Dashboard Overview": "Aperçu du tableau de bord",
      "Super Admin": "Super Admin",
      Users: "Utilisateurs",
      Transactions: "Transactions",
      Analytics: "Analytiques",
      Documents: "Documents",
      Verifications: "Vérifications",
      "Total Users": "Total utilisateurs",
      "Total Providers": "Total prestataires",
      "Total Bookings": "Total réservations",
      "Total Services": "Total services",
      "Total Revenue": "Revenu total",
      "Total Amount": "Montant total",
      "Pending Verifications": "Vérifications en attente",
      "Pending Provider Verifications":
        "Vérifications de prestataires en attente",
      "No pending verifications": "Aucune vérification en attente",
      "Recent Activity": "Activité récente",
      "Recent Providers": "Prestataires récents",
      "View All Users": "Voir tous les utilisateurs",
      "View All Providers": "Voir tous les prestataires",
      "Manage Users": "Gérer les utilisateurs",
      "Manage Providers": "Gérer les prestataires",
      "Manage Services": "Gérer les services",
      "Manage Bookings": "Gérer les réservations",
      "Manage Users - Admin - Moueene": "Gérer utilisateurs - Admin - Moueene",
      "Manage Providers - Admin - Moueene":
        "Gérer prestataires - Admin - Moueene",
      "Manage Services - Admin - Moueene": "Gérer services - Admin - Moueene",
      "Manage Bookings - Admin - Moueene":
        "Gérer réservations - Admin - Moueene",
      "Analytics - Admin - Moueene": "Analytiques - Admin - Moueene",
      "Transactions - Admin - Moueene": "Transactions - Admin - Moueene",
      "Provider Documents - Admin - Moueene":
        "Documents prestataires - Admin - Moueene",
      "Provider Verifications - Admin - Moueene":
        "Vérifications prestataires - Admin - Moueene",
      Management: "Gestion",
      Approve: "Approuver",
      Reject: "Rejeter",
      "Reject Provider": "Rejeter le prestataire",
      "Rejection Reason": "Motif de rejet",
      "Provide a reason": "Fournir une raison",
      "Please provide a reason for rejection":
        "Veuillez fournir un motif de rejet",
      "Confirm Action": "Confirmer l'action",
      "Please confirm to continue": "Veuillez confirmer pour continuer",
      "This action cannot be undone": "Cette action ne peut pas être annulée",
      "This will be saved in records": "Ceci sera enregistré dans les dossiers",
      Suspend: "Suspendre",
      Suspended: "Suspendu",
      Deactivated: "Désactivé",
      Activate: "Activer",
      Delete: "Supprimer",
      Edit: "Modifier",
      Save: "Enregistrer",
      Cancel: "Annuler",
      Cancelled: "Annulé",
      Close: "Fermer",
      Actions: "Actions",
      Status: "Statut",
      "Account Status": "Statut du compte",
      "All Account Status": "Tous les statuts de compte",
      "All Statuses": "Tous les statuts",
      "All Verification Status": "Tous les statuts de vérification",
      Date: "Date",
      Amount: "Montant",
      Active: "Actif",
      Inactive: "Inactif",
      "Active Users": "Utilisateurs actifs",
      "Active Providers": "Prestataires actifs",
      "Active Services": "Services actifs",
      Verified: "Vérifié",
      Unverified: "Non vérifié",
      "Verified Emails": "Emails vérifiés",
      "Email Verified": "Email vérifié",
      Registered: "Inscrit",
      Rejected: "Rejeté",
      Pending: "En attente",
      "Pending Verification": "Vérification en attente",
      "In Progress": "En cours",
      Popular: "Populaire",
      "Popular Services": "Services populaires",
      "New This Month": "Nouveau ce mois-ci",

      // Admin Tables
      "Booking ID": "ID de réservation",
      Customer: "Client",
      Provider: "Prestataire",
      Service: "Service",
      Duration: "Durée",
      "Base Price": "Prix de base",
      "Price Type": "Type de prix",
      Experience: "Expérience",
      Category: "Catégorie",
      "Provider Details": "Détails du prestataire",
      "Provider Documents": "Documents du prestataire",
      "Provider Verifications": "Vérifications du prestataire",
      "Identity Verification": "Vérification d'identité",
      "Insurance Documents": "Documents d'assurance",
      "Document Verification System": "Système de vérification des documents",
      Verification: "Vérification",
      "Transaction History": "Historique des transactions",
      "Financial Reports": "Rapports financiers",
      Reports: "Rapports",
      "Payment Processing": "Traitement des paiements",
      "Platform Analytics": "Analytiques de la plateforme",
      "Chart visualization coming soon":
        "Visualisation graphique bientôt disponible",
      "Coming Soon": "Bientôt disponible",
      "Under Construction": "En construction",
      "No data available": "Aucune donnée disponible",

      // Error messages
      "No bookings found": "Aucune réservation trouvée",
      "No providers found": "Aucun prestataire trouvé",
      "No services found": "Aucun service trouvé",
      "No users found": "Aucun utilisateur trouvé",
      "Error loading bookings": "Erreur de chargement des réservations",
      "Error loading providers": "Erreur de chargement des prestataires",
      "Error loading services": "Erreur de chargement des services",
      "Error loading users": "Erreur de chargement des utilisateurs",
      "Error loading provider details":
        "Erreur de chargement des détails du prestataire",
      "Failed to load bookings": "Échec du chargement des réservations",
      "Failed to load providers": "Échec du chargement des prestataires",
      "Failed to load services": "Échec du chargement des services",
      "Failed to load users": "Échec du chargement des utilisateurs",

      // Services Page Additional
      "Find the Perfect Service": "Trouvez le service parfait",
      "From home cleaning to tutoring, connect with trusted professionals in your area.":
        "Du nettoyage à domicile aux cours particuliers, connectez-vous avec des professionnels de confiance dans votre région.",
      "Show Filters": "Afficher les filtres",
      "Hide Filters": "Masquer les filtres",
      "Search services...": "Rechercher des services...",
      "Search providers...": "Rechercher des prestataires...",
      "Search chats...": "Rechercher des conversations...",
      "Search for services or providers...":
        "Rechercher des services ou prestataires...",
      "Search services, bookings, customers...":
        "Rechercher des services, réservations, clients...",
      "Search for help articles...": "Rechercher des articles d'aide...",
      "Search for answers...": "Rechercher des réponses...",
      "Services - Moueene": "Services - Moueene",
      "Providers - Moueene": "Prestataires - Moueene",
      "About Us - Moueene": "À propos - Moueene",
      "Contact Us - Moueene": "Contactez-nous - Moueene",
      "Login - Moueene": "Connexion - Moueene",
      "Sign Up - Moueene": "Inscription - Moueene",
      "Dashboard - Moueene": "Tableau de bord - Moueene",
      "Provider Dashboard - Moueene": "Tableau de bord Prestataire - Moueene",
      "Edit Profile - Moueene": "Modifier le profil - Moueene",

      // Dashboard Additional
      "Welcome back!": "Bon retour !",
      "Your upcoming bookings will appear here":
        "Vos prochaines réservations apparaîtront ici",
      "No bookings yet": "Aucune réservation pour le moment",
      "Failed to load bookings": "Échec du chargement des réservations",
      "Recent Bookings": "Réservations récentes",
      "Total Spent": "Total dépensé",
      "Total Bookings": "Total des réservations",
      "Active Bookings": "Réservations actives",
      "Completed Services": "Services terminés",
      "View Profile": "Voir le profil",
      "See your public page": "Voir votre page publique",
      "Edit Profile": "Modifier le profil",
      "Save Changes": "Enregistrer les modifications",
      "Update your information": "Mettre à jour vos informations",
      "Email cannot be changed": "L'e-mail ne peut pas être modifié",
      "Verified Customer": "Client vérifié",

      // Provider Dashboard
      "Service Provider": "Prestataire de services",
      "Add Service": "Ajouter un service",
      "Add a Service": "Ajouter un service",
      "My Services": "Mes services",
      "Services Offered": "Services proposés",
      "Create a new service offering": "Créer une nouvelle offre de service",
      "Pending Requests": "Demandes en attente",
      "Pending Approval": "En attente d'approbation",
      "Completed Jobs": "Travaux terminés",
      "Average Rating": "Note moyenne",
      "Manage Schedule": "Gérer le calendrier",
      "Update your availability": "Mettre à jour votre disponibilité",
      "Booking Requests": "Demandes de réservation",
      "All Bookings": "Toutes les réservations",
      "Accept Booking": "Accepter la réservation",
      "Start Job": "Commencer le travail",
      Availability: "Disponibilité",
      "By appointment": "Sur rendez-vous",
      Weekends: "Week-ends",
      Online: "En ligne",
      Custom: "Personnalisé",
      "Profile Information": "Informations du profil",
      "Profile Settings - Provider Dashboard":
        "Paramètres du profil - Tableau de bord Prestataire",
      "Reviews - Provider Dashboard": "Avis - Tableau de bord Prestataire",
      "Verification - Provider Dashboard":
        "Vérification - Tableau de bord Prestataire",
      "Provider Profile": "Profil du prestataire",
      "Provider Profile - Moueene": "Profil du prestataire - Moueene",
      "Provider not found": "Prestataire non trouvé",
      "Return to List": "Retour à la liste",
      "About Me": "À propos de moi",
      "Basic Info": "Informations de base",
      "Contact Info": "Coordonnées",
      "Total Reviews": "Total des avis",
      "Recommendation Rate": "Taux de recommandation",
      "Response Time": "Temps de réponse",
      "Client Reviews": "Avis des clients",
      "No reviews yet.": "Pas encore d'avis.",
      "Could not load reviews.": "Impossible de charger les avis.",
      "No services yet": "Pas encore de services",
      "Could not load services.": "Impossible de charger les services.",
      "No biography available.": "Aucune biographie disponible.",
      "This provider has not listed any specific services yet.":
        "Ce prestataire n'a pas encore listé de services spécifiques.",
      "Select a service to see details":
        "Sélectionnez un service pour voir les détails",
      "Please refresh the page.": "Veuillez actualiser la page.",
      "Your Price": "Votre prix",
      "Per Item": "Par article",
      "Upload Identity Documents": "Télécharger les documents d'identité",
      "Account Verification": "Vérification du compte",
      "Current Password": "Mot de passe actuel",
      "New Password": "Nouveau mot de passe",
      "Confirm New Password": "Confirmer le nouveau mot de passe",
      "When will you arrive?": "Quand arriverez-vous ?",
      Yesterday: "Hier",

      // About Page Additional
      "Our Core Values": "Nos valeurs fondamentales",
      "Quality Excellence": "Excellence qualité",
      "Community First": "La communauté d'abord",
      Partnership: "Partenariat",
      "Join our community today": "Rejoignez notre communauté aujourd'hui",
      "Soumia Lagoune": "Soumia Lagoune",

      // Contact Page Additional
      "Contact Information": "Informations de contact",
      "Business Address": "Adresse professionnelle",
      "Customer Support": "Support client",
      "Provider Inquiry": "Demande prestataire",
      "Select a subject": "Sélectionnez un sujet",
      "General Inquiry": "Demande générale",
      "Technical Support": "Support technique",
      "Billing Question": "Question facturation",
      "Write your message here...": "Écrivez votre message ici...",
      "Type a message...": "Tapez un message...",

      // FAQ
      "Common Questions": "Questions fréquentes",
      "Frequently Asked Questions": "Questions fréquemment posées",
      "How do I book a service?": "Comment réserver un service ?",
      "How are providers verified?":
        "Comment les prestataires sont-ils vérifiés ?",
      "How do payments work?": "Comment fonctionnent les paiements ?",

      // Help & Support Pages
      "Help Center - Moueene": "Centre d'aide - Moueene",
      "Frequently Asked Questions - Moueene": "Questions fréquentes - Moueene",
      "Privacy Policy - Moueene": "Politique de confidentialité - Moueene",
      "Terms of Service - Moueene": "Conditions d'utilisation - Moueene",
      "How can we help you?": "Comment pouvons-nous vous aider ?",
      "Find answers and get the support you need":
        "Trouvez des réponses et obtenez le support dont vous avez besoin",
      "Browse by Topic": "Parcourir par sujet",
      "Getting Started": "Commencer",
      "For Providers": "Pour les prestataires",
      Payments: "Paiements",
      "Learn how to create an account and use Moueene":
        "Apprenez à créer un compte et utiliser Moueene",
      "Creating your account": "Créer votre compte",
      "Completing your profile": "Compléter votre profil",
      "Finding services": "Trouver des services",
      "How to book a service": "Comment réserver un service",
      "Managing bookings": "Gérer les réservations",
      "Rescheduling bookings": "Reprogrammer les réservations",
      "Reporting issues": "Signaler des problèmes",
      "Account Settings": "Paramètres du compte",
      "Notification settings": "Paramètres de notification",
      "Changing password": "Changer le mot de passe",
      "Updating your profile": "Mettre à jour votre profil",
      "Becoming a provider": "Devenir prestataire",
      "Verification process": "Processus de vérification",
      "Setting your rates": "Fixer vos tarifs",
      "Payment methods": "Méthodes de paiement",
      "How refunds work": "Comment fonctionnent les remboursements",
      "Viewing receipts": "Voir les reçus",
      "Safety guidelines": "Consignes de sécurité",
      "Resources for service providers": "Ressources pour les prestataires",
      "Still Need Help?": "Besoin d'aide supplémentaire ?",
      "Our support team is available to assist you":
        "Notre équipe de support est disponible pour vous aider",
      "Cancellation policy": "Politique d'annulation",
      "Payment methods, refunds, and billing":
        "Méthodes de paiement, remboursements et facturation",
      "Everything about booking and managing services":
        "Tout sur la réservation et la gestion des services",
      "Manage your account and preferences":
        "Gérer votre compte et préférences",
      "How we keep our community safe":
        "Comment nous gardons notre communauté en sécurité",

      // Placeholders
      "Enter your email": "Entrez votre e-mail",
      "Enter your password": "Entrez votre mot de passe",
      "Enter current password": "Entrez le mot de passe actuel",
      "Enter new password": "Entrez le nouveau mot de passe",
      "Confirm new password": "Confirmez le nouveau mot de passe",
      "Create a password": "Créez un mot de passe",
      "First name": "Prénom",
      "Last name": "Nom",
      "Phone number": "Numéro de téléphone",
      "Your city": "Votre ville",
      "Enter zip or city": "Code postal ou ville",
      "Select Date": "Sélectionnez une date",
      "Tell customers about your experience, skills, and services...":
        "Décrivez votre expérience, compétences et services...",
      "Describe what you offer, what's included, etc.":
        "Décrivez ce que vous offrez, ce qui est inclus, etc.",

      // Misc
      "Loading...": "Chargement...",
      Error: "Erreur",
      Success: "Succès",
      Warning: "Attention",
      Info: "Info",
      Yes: "Oui",
      No: "Non",
      Back: "Retour",
      Next: "Suivant",
      Previous: "Précédent",
      Submit: "Soumettre",
      Continue: "Continuer",
      "Learn More": "En savoir plus",
      "Read More": "Lire la suite",
      "Show More": "Afficher plus",
      "Show Less": "Afficher moins",
      "See All": "Voir tout",
      from: "de",
      to: "à",
      or: "ou",
      and: "et",
      the: "le",
      for: "pour",
      in: "dans",
      at: "à",
      by: "par",
      on: "sur",
      is: "est",
      are: "sont",
      was: "était",
      were: "étaient",
      has: "a",
      have: "ont",
      will: "sera",
      would: "serait",
      can: "peut",
      could: "pourrait",
      should: "devrait",
      must: "doit",
      may: "peut",
      might: "pourrait",
      all: "tous",
      your: "votre",
      our: "notre",
      their: "leur",
      this: "ceci",
      that: "cela",
      these: "ces",
      those: "ceux",
      here: "ici",
      there: "là",
      where: "où",
      when: "quand",
      why: "pourquoi",
      how: "comment",
      what: "quoi",
      which: "lequel",
      who: "qui",
    },
    ar: {
      // Brand - Moueene = معين
      Moueene: "معين",
      MOUEENE: "معين",
      "Moueene - Home Services Platform": "معين - منصة الخدمات المنزلية",
      "Moueene - Connect with trusted service providers for all your needs":
        "معين - تواصل مع مزودي خدمات موثوقين لجميع احتياجاتك",

      // Navigation
      Home: "الرئيسية",
      Services: "الخدمات",
      Providers: "المزودون",
      "How It Works": "كيف يعمل",
      "About Us": "من نحن",
      Contact: "اتصل بنا",
      Login: "تسجيل الدخول",
      "Sign Up": "إنشاء حساب",
      "Sign Up Now": "سجّل الآن",
      "Book Now": "احجز الآن",
      "Become a Service Provider": "كن مزود خدمة",

      // Hero Section
      "WELCOME TO MOUEENE": "مرحبًا بك في معين",
      "Your one-stop solution for all home service needs, connecting you with trusted professionals for cleaning, gardening, nursing, and more.":
        "حلك الشامل لجميع احتياجات الخدمات المنزلية، يربطك بمحترفين موثوقين للتنظيف والبستنة والتمريض والمزيد.",
      "What service do you need today?": "ما الخدمة التي تحتاجها اليوم؟",

      // Categories
      "Discover Popular Categories": "اكتشف الفئات الشائعة",
      "Browse through our most requested service categories":
        "تصفّح أكثر فئات الخدمات طلبًا",
      Childcare: "رعاية الأطفال",
      Tutoring: "دروس خصوصية",
      Cleaning: "تنظيف",
      Gardening: "بستنة",
      "Pet Care": "رعاية الحيوانات الأليفة",
      "Elderly Care": "رعاية المسنين",
      Repairs: "إصلاحات",
      "Home Services": "الخدمات المنزلية",

      // How It Works
      "Getting started with Moueene is easy": "البدء مع معين سهل",
      "Choose Service": "اختر الخدمة",
      "Browse through our categories and find the service you need":
        "تصفح فئاتنا وابحث عن الخدمة التي تحتاجها",
      "Find Provider": "ابحث عن مزود",
      "Compare profiles, reviews, and rates to find your perfect match":
        "قارن الملفات الشخصية والمراجعات والأسعار للعثور على تطابقك المثالي",
      Book: "احجز",
      "Schedule your service at a time that works best for you":
        "حدد موعد خدمتك في الوقت الذي يناسبك",
      Evaluation: "التقييم",
      "Rate your experience and help others find great service":
        "قيّم تجربتك وساعد الآخرين في العثور على خدمة رائعة",

      // Featured Services
      "Top Services": "أفضل الخدمات",
      "Explore our most booked services right now":
        "استكشف خدماتنا الأكثر حجزًا الآن",
      "Browse All Services": "تصفح جميع الخدمات",
      "Quick View": "عرض سريع",
      "Loading top services...": "جارٍ تحميل أفضل الخدمات...",
      "No services available right now.": "لا توجد خدمات متاحة حاليًا.",
      "Failed to load top services": "فشل تحميل أفضل الخدمات",

      // CTA Section
      "Ready to Get Started?": "مستعد للبدء؟",
      "Join thousands of satisfied customers and trusted service providers on Moueene":
        "انضم إلى آلاف العملاء الراضين ومزودي الخدمات الموثوقين في معين",
      "Contact Us": "اتصل بنا",

      // Footer
      "Quick Links": "روابط سريعة",
      "Find Providers": "ابحث عن مزودين",
      Support: "الدعم",
      FAQ: "الأسئلة الشائعة",
      "Privacy Policy": "سياسة الخصوصية",
      "Terms of Service": "شروط الخدمة",
      "Help Center": "مركز المساعدة",
      "All rights reserved.": "جميع الحقوق محفوظة.",
      "© 2026 Moueene. All rights reserved.":
        "© 2026 معين. جميع الحقوق محفوظة.",
      "2026 Moueene": "2026 معين",
      "Moueene connects customers with trusted service providers for all their home and personal needs.":
        "معين يربط العملاء بمزودي خدمات موثوقين لجميع احتياجاتهم المنزلية والشخصية.",

      // Auth Pages
      "Welcome Back": "مرحبًا بعودتك",
      "Welcome back": "مرحبًا بعودتك",
      "Login to manage your account": "سجّل الدخول لإدارة حسابك",
      Customer: "عميل",
      Provider: "مزود",
      "Admin Portal": "بوابة الإدارة",
      "Sign in to manage the platform": "سجّل الدخول لإدارة المنصة",
      "Email Address": "البريد الإلكتروني",
      Email: "البريد الإلكتروني",
      Password: "كلمة المرور",
      "Enter your password": "أدخل كلمة المرور",
      "Sign In": "دخول",
      "Signing in...": "جارٍ تسجيل الدخول...",
      "Back to main site": "العودة إلى الموقع",
      "Invalid credentials": "بيانات الدخول غير صحيحة",
      "Connection error. Please try again.":
        "خطأ في الاتصال. يرجى المحاولة مرة أخرى.",
      "Remember me": "تذكرني",
      "Forgot Password?": "نسيت كلمة المرور؟",
      "Or continue with": "أو تابع مع",
      "Don't have an account?": "ليس لديك حساب؟",
      "Already have an account?": "لديك حساب بالفعل؟",
      "Connecting you with trusted professionals for all your home service needs.":
        "نربطك بمحترفين موثوقين لجميع احتياجات خدماتك المنزلية.",

      // Registration
      "Create Account": "إنشاء حساب",
      "Join Moueene today": "انضم إلى معين اليوم",
      "First Name": "الاسم الأول",
      "Last Name": "اسم العائلة",
      "Phone Number": "رقم الهاتف",
      "Confirm Password": "تأكيد كلمة المرور",
      "I agree to the": "أوافق على",
      "Terms and Conditions": "الشروط والأحكام",
      and: "و",
      "Create your account": "أنشئ حسابك",
      "hello@example.com": "مثال@example.com",

      // Dashboard
      "Loading bookings...": "جارٍ تحميل الحجوزات...",
      "No active bookings found.": "لا توجد حجوزات نشطة.",
      Dashboard: "لوحة التحكم",
      Bookings: "الحجوزات",
      Messages: "الرسائل",
      Profile: "الملف الشخصي",
      Settings: "الإعدادات",
      Logout: "تسجيل الخروج",
      Search: "بحث",
      User: "مستخدم",
      with: "مع",
      Pending: "قيد الانتظار",
      Confirmed: "مؤكد",
      Completed: "مكتمل",
      Cancelled: "ملغي",
      "Active Bookings": "الحجوزات النشطة",
      "Completed Bookings": "الحجوزات المكتملة",
      "My Bookings": "حجوزاتي",
      Favorites: "المفضلة",
      Notifications: "الإشعارات",
      "View All": "عرض الكل",

      // Validation
      "This field is required": "هذا الحقل مطلوب",
      "Please enter a valid email address": "يرجى إدخال بريد إلكتروني صحيح",
      "Passwords do not match": "كلمتا المرور غير متطابقتين",
      "Registration failed. Please try again.":
        "فشل التسجيل. يرجى المحاولة مرة أخرى.",
      "Login failed. Please try again.":
        "فشل تسجيل الدخول. يرجى المحاولة مرة أخرى.",
      "Failed to update profile.": "فشل تحديث الملف الشخصي.",
      "Failed to change password.": "فشل تغيير كلمة المرور.",

      // Access
      "Access denied": "تم رفض الوصول",
      "You do not have permission to view this page.":
        "ليست لديك صلاحية لعرض هذه الصفحة.",
      Message: "رسالة",
      OK: "حسنًا",

      // Provider Status
      Account: "الحساب",
      "Account Suspended": "تم تعليق الحساب",
      "Account Deactivated": "تم إلغاء تفعيل الحساب",
      "Verified Provider": "مزود موثوق",
      "Verification Rejected": "تم رفض التحقق",
      "Pending Verification": "التحقق قيد الانتظار",

      // Services Page
      "All Services": "جميع الخدمات",
      "Find the perfect service for your needs":
        "ابحث عن الخدمة المثالية لاحتياجاتك",
      Filter: "تصفية",
      Filters: "عوامل التصفية",
      Category: "الفئة",
      Categories: "الفئات",
      "All Categories": "جميع الفئات",
      "Price Range": "نطاق السعر",
      Min: "الحد الأدنى",
      Max: "الحد الأقصى",
      Rating: "التقييم",
      "Apply Filters": "تطبيق الفلاتر",
      "Clear Filters": "مسح الفلاتر",
      "Sort by": "ترتيب حسب",
      "Most Popular": "الأكثر شعبية",
      Newest: "الأحدث",
      "Price: Low to High": "السعر: من الأقل للأعلى",
      "Price: High to Low": "السعر: من الأعلى للأقل",
      "Highest Rated": "الأعلى تقييمًا",
      reviews: "مراجعات",
      "No services found": "لم يتم العثور على خدمات",
      "per hour": "في الساعة",
      "/hr": "/ساعة",
      "/hour": "/ساعة",
      "Starting from": "بدءًا من",
      "Book Service": "احجز الخدمة",
      "View Details": "عرض التفاصيل",

      // About Page
      "About Moueene": "عن معين",
      "Our Story": "قصتنا",
      "Our Mission": "مهمتنا",
      "Our Vision": "رؤيتنا",
      "Our Values": "قيمنا",
      "Meet Our Team": "تعرف على فريقنا",
      Trust: "الثقة",
      Quality: "الجودة",
      Innovation: "الابتكار",
      Community: "المجتمع",

      // Contact Page
      "Get in Touch": "تواصل معنا",
      "Send us a message": "أرسل لنا رسالة",
      "Your Name": "اسمك",
      "Your Email": "بريدك الإلكتروني",
      Subject: "الموضوع",
      "Your Message": "رسالتك",
      "Send Message": "إرسال الرسالة",
      Address: "العنوان",
      Phone: "الهاتف",

      // Admin
      "Admin Dashboard": "لوحة تحكم الإدارة",
      "Admin Panel": "لوحة الإدارة",
      "Admin Dashboard - Moueene": "لوحة تحكم الإدارة - معين",
      "Admin Login - Moueene": "تسجيل دخول الإدارة - معين",
      "Dashboard Overview": "نظرة عامة على لوحة التحكم",
      "Super Admin": "المسؤول الأعلى",
      Users: "المستخدمون",
      Transactions: "المعاملات",
      Analytics: "التحليلات",
      Documents: "المستندات",
      Verifications: "التحققات",
      "Total Users": "إجمالي المستخدمين",
      "Total Providers": "إجمالي المزودين",
      "Total Bookings": "إجمالي الحجوزات",
      "Total Services": "إجمالي الخدمات",
      "Total Revenue": "إجمالي الإيرادات",
      "Total Amount": "المبلغ الإجمالي",
      "Pending Verifications": "التحققات المعلقة",
      "Pending Provider Verifications": "تحققات المزودين المعلقة",
      "No pending verifications": "لا توجد تحققات معلقة",
      "Recent Activity": "النشاط الأخير",
      "Recent Providers": "المزودون الأخيرون",
      "View All Users": "عرض جميع المستخدمين",
      "View All Providers": "عرض جميع المزودين",
      "Manage Users": "إدارة المستخدمين",
      "Manage Providers": "إدارة المزودين",
      "Manage Services": "إدارة الخدمات",
      "Manage Bookings": "إدارة الحجوزات",
      "Manage Users - Admin - Moueene": "إدارة المستخدمين - الإدارة - معين",
      "Manage Providers - Admin - Moueene": "إدارة المزودين - الإدارة - معين",
      "Manage Services - Admin - Moueene": "إدارة الخدمات - الإدارة - معين",
      "Manage Bookings - Admin - Moueene": "إدارة الحجوزات - الإدارة - معين",
      "Analytics - Admin - Moueene": "التحليلات - الإدارة - معين",
      "Transactions - Admin - Moueene": "المعاملات - الإدارة - معين",
      "Provider Documents - Admin - Moueene":
        "مستندات المزودين - الإدارة - معين",
      "Provider Verifications - Admin - Moueene":
        "تحققات المزودين - الإدارة - معين",
      Management: "الإدارة",
      Approve: "موافقة",
      Reject: "رفض",
      "Reject Provider": "رفض المزود",
      "Rejection Reason": "سبب الرفض",
      "Provide a reason": "قدّم سببًا",
      "Please provide a reason for rejection": "يرجى تقديم سبب للرفض",
      "Confirm Action": "تأكيد الإجراء",
      "Please confirm to continue": "يرجى التأكيد للمتابعة",
      "This action cannot be undone": "لا يمكن التراجع عن هذا الإجراء",
      "This will be saved in records": "سيتم حفظ هذا في السجلات",
      Suspend: "تعليق",
      Suspended: "معلق",
      Deactivated: "معطّل",
      Activate: "تفعيل",
      Delete: "حذف",
      Edit: "تعديل",
      Save: "حفظ",
      Cancel: "إلغاء",
      Cancelled: "ملغي",
      Close: "إغلاق",
      Actions: "الإجراءات",
      Status: "الحالة",
      "Account Status": "حالة الحساب",
      "All Account Status": "جميع حالات الحساب",
      "All Statuses": "جميع الحالات",
      "All Verification Status": "جميع حالات التحقق",
      Date: "التاريخ",
      Amount: "المبلغ",
      Active: "نشط",
      Inactive: "غير نشط",
      "Active Users": "المستخدمون النشطون",
      "Active Providers": "المزودون النشطون",
      "Active Services": "الخدمات النشطة",
      Verified: "موثق",
      Unverified: "غير موثق",
      "Verified Emails": "الإيميلات الموثقة",
      "Email Verified": "البريد موثق",
      Registered: "مسجّل",
      Rejected: "مرفوض",
      Pending: "قيد الانتظار",
      "Pending Verification": "التحقق قيد الانتظار",
      "In Progress": "قيد التنفيذ",
      Popular: "شائع",
      "Popular Services": "الخدمات الشائعة",
      "New This Month": "جديد هذا الشهر",

      // Admin Tables
      "Booking ID": "معرف الحجز",
      Customer: "عميل",
      Provider: "مزود",
      Service: "خدمة",
      Duration: "المدة",
      "Base Price": "السعر الأساسي",
      "Price Type": "نوع السعر",
      Experience: "الخبرة",
      Category: "الفئة",
      "Provider Details": "تفاصيل المزود",
      "Provider Documents": "مستندات المزود",
      "Provider Verifications": "تحققات المزود",
      "Identity Verification": "التحقق من الهوية",
      "Insurance Documents": "مستندات التأمين",
      "Document Verification System": "نظام التحقق من المستندات",
      Verification: "التحقق",
      "Transaction History": "سجل المعاملات",
      "Financial Reports": "التقارير المالية",
      Reports: "التقارير",
      "Payment Processing": "معالجة الدفع",
      "Platform Analytics": "تحليلات المنصة",
      "Chart visualization coming soon": "تصور الرسم البياني قريبًا",
      "Coming Soon": "قريبًا",
      "Under Construction": "تحت الإنشاء",
      "No data available": "لا توجد بيانات متاحة",

      // Error messages
      "No bookings found": "لم يتم العثور على حجوزات",
      "No providers found": "لم يتم العثور على مزودين",
      "No services found": "لم يتم العثور على خدمات",
      "No users found": "لم يتم العثور على مستخدمين",
      "Error loading bookings": "خطأ في تحميل الحجوزات",
      "Error loading providers": "خطأ في تحميل المزودين",
      "Error loading services": "خطأ في تحميل الخدمات",
      "Error loading users": "خطأ في تحميل المستخدمين",
      "Error loading provider details": "خطأ في تحميل تفاصيل المزود",
      "Failed to load bookings": "فشل تحميل الحجوزات",
      "Failed to load providers": "فشل تحميل المزودين",
      "Failed to load services": "فشل تحميل الخدمات",
      "Failed to load users": "فشل تحميل المستخدمين",

      // Services Page Additional
      "Find the Perfect Service": "ابحث عن الخدمة المثالية",
      "From home cleaning to tutoring, connect with trusted professionals in your area.":
        "من تنظيف المنزل إلى الدروس الخصوصية، تواصل مع محترفين موثوقين في منطقتك.",
      "Show Filters": "إظهار الفلاتر",
      "Hide Filters": "إخفاء الفلاتر",
      "Search services...": "ابحث عن خدمات...",
      "Search providers...": "ابحث عن مزودين...",
      "Search chats...": "ابحث في المحادثات...",
      "Search for services or providers...": "ابحث عن خدمات أو مزودين...",
      "Search services, bookings, customers...":
        "ابحث عن خدمات، حجوزات، عملاء...",
      "Search for help articles...": "ابحث عن مقالات المساعدة...",
      "Search for answers...": "ابحث عن إجابات...",
      "Services - Moueene": "الخدمات - معين",
      "Providers - Moueene": "المزودون - معين",
      "About Us - Moueene": "من نحن - معين",
      "Contact Us - Moueene": "اتصل بنا - معين",
      "Login - Moueene": "تسجيل الدخول - معين",
      "Sign Up - Moueene": "إنشاء حساب - معين",
      "Dashboard - Moueene": "لوحة التحكم - معين",
      "Provider Dashboard - Moueene": "لوحة تحكم المزود - معين",
      "Edit Profile - Moueene": "تعديل الملف الشخصي - معين",

      // Dashboard Additional
      "Welcome back!": "مرحبًا بعودتك!",
      "Your upcoming bookings will appear here": "ستظهر حجوزاتك القادمة هنا",
      "No bookings yet": "لا توجد حجوزات بعد",
      "Failed to load bookings": "فشل تحميل الحجوزات",
      "Recent Bookings": "الحجوزات الأخيرة",
      "Total Spent": "إجمالي المصروفات",
      "Total Bookings": "إجمالي الحجوزات",
      "Active Bookings": "الحجوزات النشطة",
      "Completed Services": "الخدمات المكتملة",
      "View Profile": "عرض الملف الشخصي",
      "See your public page": "شاهد صفحتك العامة",
      "Edit Profile": "تعديل الملف الشخصي",
      "Save Changes": "حفظ التغييرات",
      "Update your information": "تحديث معلوماتك",
      "Email cannot be changed": "لا يمكن تغيير البريد الإلكتروني",
      "Verified Customer": "عميل موثق",

      // Provider Dashboard
      "Service Provider": "مزود خدمة",
      "Add Service": "إضافة خدمة",
      "Add a Service": "إضافة خدمة",
      "My Services": "خدماتي",
      "Services Offered": "الخدمات المقدمة",
      "Create a new service offering": "إنشاء عرض خدمة جديد",
      "Pending Requests": "الطلبات المعلقة",
      "Pending Approval": "في انتظار الموافقة",
      "Completed Jobs": "الأعمال المكتملة",
      "Average Rating": "متوسط التقييم",
      "Manage Schedule": "إدارة الجدول",
      "Update your availability": "تحديث توفرك",
      "Booking Requests": "طلبات الحجز",
      "All Bookings": "جميع الحجوزات",
      "Accept Booking": "قبول الحجز",
      "Start Job": "بدء العمل",
      Availability: "التوفر",
      "By appointment": "بموعد مسبق",
      Weekends: "عطلات نهاية الأسبوع",
      Online: "متصل",
      Custom: "مخصص",
      "Profile Information": "معلومات الملف الشخصي",
      "Profile Settings - Provider Dashboard":
        "إعدادات الملف - لوحة تحكم المزود",
      "Reviews - Provider Dashboard": "التقييمات - لوحة تحكم المزود",
      "Verification - Provider Dashboard": "التحقق - لوحة تحكم المزود",
      "Provider Profile": "ملف المزود",
      "Provider Profile - Moueene": "ملف المزود - معين",
      "Provider not found": "المزود غير موجود",
      "Return to List": "العودة إلى القائمة",
      "About Me": "عني",
      "Basic Info": "معلومات أساسية",
      "Contact Info": "معلومات الاتصال",
      "Total Reviews": "إجمالي التقييمات",
      "Recommendation Rate": "معدل التوصية",
      "Response Time": "وقت الاستجابة",
      "Client Reviews": "تقييمات العملاء",
      "No reviews yet.": "لا توجد تقييمات بعد.",
      "Could not load reviews.": "تعذر تحميل التقييمات.",
      "No services yet": "لا توجد خدمات بعد",
      "Could not load services.": "تعذر تحميل الخدمات.",
      "No biography available.": "لا توجد سيرة ذاتية متاحة.",
      "This provider has not listed any specific services yet.":
        "لم يقم هذا المزود بإدراج خدمات محددة بعد.",
      "Select a service to see details": "اختر خدمة لرؤية التفاصيل",
      "Please refresh the page.": "يرجى تحديث الصفحة.",
      "Your Price": "سعرك",
      "Per Item": "لكل عنصر",
      "Upload Identity Documents": "تحميل مستندات الهوية",
      "Account Verification": "التحقق من الحساب",
      "Current Password": "كلمة المرور الحالية",
      "New Password": "كلمة المرور الجديدة",
      "Confirm New Password": "تأكيد كلمة المرور الجديدة",
      "When will you arrive?": "متى ستصل؟",
      Yesterday: "أمس",

      // About Page Additional
      "Our Core Values": "قيمنا الأساسية",
      "Quality Excellence": "التميز في الجودة",
      "Community First": "المجتمع أولاً",
      Partnership: "الشراكة",
      "Join our community today": "انضم إلى مجتمعنا اليوم",
      "Soumia Lagoune": "سمية لاغون",

      // Contact Page Additional
      "Contact Information": "معلومات الاتصال",
      "Business Address": "عنوان العمل",
      "Customer Support": "دعم العملاء",
      "Provider Inquiry": "استفسار المزود",
      "Select a subject": "اختر موضوعًا",
      "General Inquiry": "استفسار عام",
      "Technical Support": "الدعم الفني",
      "Billing Question": "سؤال عن الفواتير",
      "Write your message here...": "اكتب رسالتك هنا...",
      "Type a message...": "اكتب رسالة...",

      // FAQ
      "Common Questions": "أسئلة شائعة",
      "Frequently Asked Questions": "الأسئلة المتكررة",
      "How do I book a service?": "كيف أحجز خدمة؟",
      "How are providers verified?": "كيف يتم التحقق من المزودين؟",
      "How do payments work?": "كيف تعمل المدفوعات؟",

      // Help & Support Pages
      "Help Center - Moueene": "مركز المساعدة - معين",
      "Frequently Asked Questions - Moueene": "الأسئلة المتكررة - معين",
      "Privacy Policy - Moueene": "سياسة الخصوصية - معين",
      "Terms of Service - Moueene": "شروط الخدمة - معين",
      "How can we help you?": "كيف يمكننا مساعدتك؟",
      "Find answers and get the support you need":
        "ابحث عن إجابات واحصل على الدعم الذي تحتاجه",
      "Browse by Topic": "تصفح حسب الموضوع",
      "Getting Started": "البداية",
      "For Providers": "للمزودين",
      Payments: "المدفوعات",
      "Learn how to create an account and use Moueene":
        "تعرف على كيفية إنشاء حساب واستخدام معين",
      "Creating your account": "إنشاء حسابك",
      "Completing your profile": "إكمال ملفك الشخصي",
      "Finding services": "البحث عن خدمات",
      "How to book a service": "كيفية حجز خدمة",
      "Managing bookings": "إدارة الحجوزات",
      "Rescheduling bookings": "إعادة جدولة الحجوزات",
      "Reporting issues": "الإبلاغ عن مشاكل",
      "Account Settings": "إعدادات الحساب",
      "Notification settings": "إعدادات الإشعارات",
      "Changing password": "تغيير كلمة المرور",
      "Updating your profile": "تحديث ملفك الشخصي",
      "Becoming a provider": "أن تصبح مزوداً",
      "Verification process": "عملية التحقق",
      "Setting your rates": "تحديد أسعارك",
      "Payment methods": "طرق الدفع",
      "How refunds work": "كيف تعمل المبالغ المستردة",
      "Viewing receipts": "عرض الإيصالات",
      "Safety guidelines": "إرشادات السلامة",
      "Resources for service providers": "موارد لمزودي الخدمات",
      "Still Need Help?": "لا تزال بحاجة للمساعدة؟",
      "Our support team is available to assist you":
        "فريق الدعم لدينا متاح لمساعدتك",
      "Cancellation policy": "سياسة الإلغاء",
      "Payment methods, refunds, and billing":
        "طرق الدفع والمبالغ المستردة والفواتير",
      "Everything about booking and managing services":
        "كل شيء عن حجز وإدارة الخدمات",
      "Manage your account and preferences": "إدارة حسابك وتفضيلاتك",
      "How we keep our community safe": "كيف نحافظ على أمان مجتمعنا",

      // Placeholders
      "Enter your email": "أدخل بريدك الإلكتروني",
      "Enter your password": "أدخل كلمة المرور",
      "Enter current password": "أدخل كلمة المرور الحالية",
      "Enter new password": "أدخل كلمة المرور الجديدة",
      "Confirm new password": "تأكيد كلمة المرور الجديدة",
      "Create a password": "أنشئ كلمة مرور",
      "First name": "الاسم الأول",
      "Last name": "اسم العائلة",
      "Phone number": "رقم الهاتف",
      "Your city": "مدينتك",
      "Enter zip or city": "أدخل الرمز البريدي أو المدينة",
      "Select Date": "اختر التاريخ",
      "Tell customers about your experience, skills, and services...":
        "أخبر العملاء عن خبرتك ومهاراتك وخدماتك...",
      "Describe what you offer, what's included, etc.":
        "صِف ما تقدمه، ما هو مشمول، إلخ...",

      // Misc
      "Loading...": "جارٍ التحميل...",
      Error: "خطأ",
      Success: "نجاح",
      Warning: "تحذير",
      Info: "معلومات",
      Yes: "نعم",
      No: "لا",
      Back: "رجوع",
      Next: "التالي",
      Previous: "السابق",
      Submit: "إرسال",
      Continue: "متابعة",
      "Learn More": "اعرف المزيد",
      "Read More": "اقرأ المزيد",
      "Show More": "عرض المزيد",
      "Show Less": "عرض أقل",
      "See All": "مشاهدة الكل",
      from: "من",
      to: "إلى",
      or: "أو",
      and: "و",
      the: "ال",
      for: "لـ",
      in: "في",
      at: "في",
      by: "بواسطة",
      on: "على",
      is: "هو",
      are: "هم",
      was: "كان",
      were: "كانوا",
      has: "لديه",
      have: "لديهم",
      will: "سوف",
      would: "سيكون",
      can: "يستطيع",
      could: "يمكن",
      should: "يجب",
      must: "يجب",
      may: "قد",
      might: "ربما",
      all: "الكل",
      your: "لك",
      our: "لنا",
      their: "لهم",
      this: "هذا",
      that: "ذلك",
      these: "هؤلاء",
      those: "أولئك",
      here: "هنا",
      there: "هناك",
      where: "أين",
      when: "متى",
      why: "لماذا",
      how: "كيف",
      what: "ماذا",
      which: "أي",
      who: "من",
    },
  };

  // Word-level translations (fallback to increase coverage)
  const WORDS = {
    fr: {
      // Brand
      moueene: "moueene",

      // Common words
      home: "accueil",
      service: "service",
      services: "services",
      provider: "prestataire",
      providers: "prestataires",
      booking: "réservation",
      bookings: "réservations",
      message: "message",
      messages: "messages",
      dashboard: "tableau de bord",
      profile: "profil",
      settings: "paramètres",
      contact: "contact",
      about: "à propos",
      login: "connexion",
      logout: "déconnexion",
      register: "inscription",
      sign: "inscription",
      search: "rechercher",
      help: "aide",
      privacy: "confidentialité",
      terms: "conditions",
      admin: "admin",
      user: "utilisateur",
      users: "utilisateurs",
      customer: "client",
      customers: "clients",
      password: "mot de passe",
      email: "e-mail",
      address: "adresse",
      welcome: "bienvenue",
      back: "retour",
      loading: "chargement",
      no: "aucun",
      yes: "oui",
      active: "actif",

      // Additional common words
      connecting: "connectant",
      solution: "solution",
      needs: "besoins",
      trusted: "confiance",
      professionals: "professionnels",
      nursing: "soins infirmiers",
      more: "plus",
      stop: "étape",
      one: "un",
      inactive: "inactif",
      found: "trouvé",
      with: "avec",
      pending: "en attente",
      confirmed: "confirmé",
      completed: "terminé",
      cancelled: "annulé",
      connection: "connexion",
      error: "erreur",
      success: "succès",
      warning: "attention",
      please: "veuillez",
      try: "essayer",
      again: "encore",
      invalid: "invalide",
      credentials: "identifiants",

      // Categories
      childcare: "garde d'enfants",
      tutoring: "cours particuliers",
      cleaning: "nettoyage",
      gardening: "jardinage",
      repairs: "réparations",
      elderly: "personnes âgées",
      pet: "animal",
      care: "soins",
      nursing: "soins infirmiers",

      // Actions
      book: "réserver",
      cancel: "annuler",
      edit: "modifier",
      delete: "supprimer",
      save: "enregistrer",
      submit: "soumettre",
      send: "envoyer",
      view: "voir",
      browse: "parcourir",
      explore: "explorer",
      discover: "découvrir",
      find: "trouver",
      choose: "choisir",
      select: "sélectionner",
      apply: "appliquer",
      clear: "effacer",
      close: "fermer",
      open: "ouvrir",
      create: "créer",
      update: "mettre à jour",
      manage: "gérer",

      // Adjectives
      popular: "populaire",
      trusted: "confiance",
      professional: "professionnel",
      professionals: "professionnels",
      verified: "vérifié",
      top: "meilleurs",
      best: "meilleur",
      new: "nouveau",
      all: "tous",
      more: "plus",
      less: "moins",

      // Time
      today: "aujourd'hui",
      now: "maintenant",
      soon: "bientôt",
      date: "date",
      time: "heure",
      hour: "heure",
      hours: "heures",
      day: "jour",
      days: "jours",
      week: "semaine",
      month: "mois",
      year: "année",

      // Other
      name: "nom",
      first: "prénom",
      last: "famille",
      phone: "téléphone",
      price: "prix",
      rating: "note",
      review: "avis",
      reviews: "avis",
      category: "catégorie",
      categories: "catégories",
      filter: "filtre",
      filters: "filtres",
      sort: "trier",
      total: "total",
      amount: "montant",
      status: "statut",
      details: "détails",
      information: "informations",
      description: "description",
      platform: "plateforme",
      solution: "solution",
      needs: "besoins",
      quick: "rapide",
      links: "liens",
      support: "assistance",
      center: "centre",
      reserved: "réservés",
      rights: "droits",
      connects: "connecte",
      personal: "personnel",
      account: "compte",
      portal: "portail",

      // Pronouns & articles
      the: "le",
      a: "un",
      an: "un",
      this: "ce",
      that: "ce",
      your: "votre",
      our: "notre",
      my: "mon",

      // Prepositions & conjunctions
      to: "à",
      for: "pour",
      from: "de",
      in: "dans",
      on: "sur",
      at: "à",
      by: "par",
      of: "de",
      and: "et",
      or: "ou",
      but: "mais",

      // Verbs
      is: "est",
      are: "sont",
      get: "obtenir",
      started: "commencé",
      getting: "commencer",
      join: "rejoindre",
      thousands: "milliers",
      satisfied: "satisfaits",
    },
    ar: {
      // Brand - Moueene = معين
      moueene: "معين",

      // Common words
      home: "الرئيسية",
      service: "خدمة",
      services: "الخدمات",
      provider: "مزود",
      providers: "المزودون",
      booking: "حجز",
      bookings: "الحجوزات",
      message: "رسالة",
      messages: "الرسائل",
      dashboard: "لوحة التحكم",
      profile: "الملف الشخصي",
      settings: "الإعدادات",
      contact: "اتصل",
      about: "حول",
      login: "دخول",
      logout: "خروج",
      register: "تسجيل",
      sign: "تسجيل",
      search: "بحث",
      help: "مساعدة",
      privacy: "الخصوصية",
      terms: "الشروط",
      admin: "إدارة",
      user: "مستخدم",
      users: "المستخدمون",
      customer: "عميل",
      customers: "العملاء",
      password: "كلمة المرور",
      email: "البريد",
      address: "العنوان",
      welcome: "مرحبًا",
      back: "عودة",
      loading: "جارٍ التحميل",
      no: "لا",
      yes: "نعم",
      active: "نشط",
      inactive: "غير نشط",
      found: "موجود",
      with: "مع",

      // Additional common words
      connecting: "يربطك",
      solution: "حل",
      needs: "احتياجات",
      trusted: "موثوق",
      professionals: "محترفون",
      nursing: "تمريض",
      more: "المزيد",
      stop: "محطة",
      one: "واحد",
      your: "لك",
      you: "أنت",
      all: "جميع",
      for: "لـ",
      and: "و",
      pending: "قيد الانتظار",
      confirmed: "مؤكد",
      completed: "مكتمل",
      cancelled: "ملغي",
      connection: "اتصال",
      error: "خطأ",
      success: "نجاح",
      warning: "تحذير",
      please: "يرجى",
      try: "حاول",
      again: "مرة أخرى",
      invalid: "غير صالح",
      credentials: "بيانات الدخول",

      // Categories
      childcare: "رعاية الأطفال",
      tutoring: "دروس خصوصية",
      cleaning: "تنظيف",
      gardening: "بستنة",
      repairs: "إصلاحات",
      elderly: "كبار السن",
      pet: "حيوان أليف",
      care: "رعاية",
      nursing: "تمريض",

      // Actions
      book: "احجز",
      cancel: "إلغاء",
      edit: "تعديل",
      delete: "حذف",
      save: "حفظ",
      submit: "إرسال",
      send: "إرسال",
      view: "عرض",
      browse: "تصفح",
      explore: "استكشف",
      discover: "اكتشف",
      find: "ابحث",
      choose: "اختر",
      select: "حدد",
      apply: "تطبيق",
      clear: "مسح",
      close: "إغلاق",
      open: "فتح",
      create: "إنشاء",
      update: "تحديث",
      manage: "إدارة",

      // Adjectives
      popular: "شائع",
      trusted: "موثوق",
      professional: "محترف",
      professionals: "محترفون",
      verified: "موثق",
      top: "أفضل",
      best: "الأفضل",
      new: "جديد",
      all: "الكل",
      more: "المزيد",
      less: "أقل",

      // Time
      today: "اليوم",
      now: "الآن",
      soon: "قريبًا",
      date: "تاريخ",
      time: "وقت",
      hour: "ساعة",
      hours: "ساعات",
      day: "يوم",
      days: "أيام",
      week: "أسبوع",
      month: "شهر",
      year: "سنة",

      // Other
      name: "اسم",
      first: "الأول",
      last: "العائلة",
      phone: "هاتف",
      price: "سعر",
      rating: "تقييم",
      review: "مراجعة",
      reviews: "مراجعات",
      category: "فئة",
      categories: "الفئات",
      filter: "تصفية",
      filters: "فلاتر",
      sort: "ترتيب",
      total: "إجمالي",
      amount: "مبلغ",
      status: "حالة",
      details: "تفاصيل",
      information: "معلومات",
      description: "وصف",
      platform: "منصة",
      solution: "حل",
      needs: "احتياجات",
      quick: "سريع",
      links: "روابط",
      support: "دعم",
      center: "مركز",
      reserved: "محفوظة",
      rights: "حقوق",
      connects: "يربط",
      personal: "شخصي",
      account: "حساب",
      portal: "بوابة",

      // Pronouns & articles
      the: "ال",
      a: "",
      an: "",
      this: "هذا",
      that: "ذلك",
      your: "لك",
      our: "لنا",
      my: "لي",

      // Prepositions & conjunctions
      to: "إلى",
      for: "لـ",
      from: "من",
      in: "في",
      on: "على",
      at: "في",
      by: "بواسطة",
      of: "من",
      and: "و",
      or: "أو",
      but: "لكن",

      // Verbs
      is: "هو",
      are: "هم",
      get: "احصل",
      started: "البدء",
      getting: "البدء",
      join: "انضم",
      thousands: "آلاف",
      satisfied: "راضون",
    },
  };

  const textNodeOriginal = new WeakMap();
  const elementAttrOriginal = new WeakMap(); // element -> { attrName: originalValue }

  let currentLang = "en";
  let mutationObserver = null;
  let initialized = false;

  function preserveOuterWhitespace(original, translatedCore) {
    const leading = (String(original).match(/^\s*/u) || [""])[0];
    const trailing = (String(original).match(/\s*$/u) || [""])[0];
    return leading + translatedCore + trailing;
  }

  function isAllCaps(word) {
    const hasLetter = /[A-Z]/.test(word) || /[a-z]/.test(word);
    return hasLetter && word === word.toUpperCase();
  }

  function isCapitalized(word) {
    return /^[A-Z][a-z]/.test(word);
  }

  function applyLatinCasing(sourceToken, translatedToken) {
    if (currentLang === "ar") return translatedToken;
    if (!translatedToken) return translatedToken;
    if (isAllCaps(sourceToken)) return translatedToken.toUpperCase();
    if (isCapitalized(sourceToken)) {
      return translatedToken.charAt(0).toUpperCase() + translatedToken.slice(1);
    }
    return translatedToken;
  }

  function translateToken(token) {
    if (!token || !/[\p{L}]/u.test(token)) return token;
    const lower = token.toLowerCase();
    const wordMap = WORDS[currentLang];
    if (!wordMap) return token;
    const translated = wordMap[lower];
    if (!translated) return token;
    return applyLatinCasing(token, translated);
  }

  function translateCore(core) {
    if (currentLang === "en") return core;
    const phraseMap = PHRASES[currentLang] || {};

    // First try exact match
    const exact = phraseMap[core];
    if (exact) return exact;

    // Try with normalized whitespace (collapse multiple spaces/newlines to single space)
    const normalized = core.replace(/\s+/g, " ").trim();
    const normalizedMatch = phraseMap[normalized];
    if (normalizedMatch) return normalizedMatch;

    const tokens = core.match(
      /[\p{L}]+(?:'[\p{L}]+)?|[\p{N}]+|[^\p{L}\p{N}]+/gu,
    );
    if (!tokens) return core;

    return tokens
      .map((t) => {
        if (phraseMap[t]) return phraseMap[t];
        return translateToken(t);
      })
      .join("");
  }

  function translateString(original) {
    const core = String(original);
    const trimmed = core.trim();
    if (!trimmed) return core;
    const translatedCore = translateCore(trimmed);
    return preserveOuterWhitespace(core, translatedCore);
  }

  function shouldIgnoreNode(node) {
    const parent = node && node.parentElement;
    if (!parent) return false;
    if (parent.closest("[data-i18n-ignore]")) return true;
    const tag = parent.tagName ? parent.tagName.toLowerCase() : "";
    return tag === "script" || tag === "style" || tag === "noscript";
  }

  function translateTextNode(node) {
    if (!node || node.nodeType !== Node.TEXT_NODE) return;
    if (shouldIgnoreNode(node)) return;

    const original = textNodeOriginal.get(node) ?? node.nodeValue;
    if (original == null) return;
    if (!textNodeOriginal.has(node)) textNodeOriginal.set(node, original);

    const translated = translateString(original);
    if (node.nodeValue !== translated) node.nodeValue = translated;
  }

  function captureElementOriginalAttr(el, attr) {
    const current = el.getAttribute(attr);
    if (current == null) return;
    let record = elementAttrOriginal.get(el);
    if (!record) {
      record = {};
      elementAttrOriginal.set(el, record);
    }
    if (!(attr in record)) record[attr] = current;
  }

  function translateElementAttributes(el) {
    if (!el || el.nodeType !== Node.ELEMENT_NODE) return;
    if (el.closest && el.closest("[data-i18n-ignore]")) return;

    const attrs = ["placeholder", "title", "aria-label", "alt", "value"];
    // Meta descriptions & social titles live in the 'content' attribute
    if (el.tagName === "META") {
      const name = (el.getAttribute("name") || "").toLowerCase();
      const property = (el.getAttribute("property") || "").toLowerCase();
      if (
        name === "description" ||
        name === "keywords" ||
        property === "og:title" ||
        property === "og:description" ||
        property === "twitter:title" ||
        property === "twitter:description"
      ) {
        attrs.push("content");
      }
    }
    for (const attr of attrs) {
      if (!el.hasAttribute || !el.hasAttribute(attr)) continue;
      captureElementOriginalAttr(el, attr);
      const record = elementAttrOriginal.get(el);
      const original = record ? record[attr] : el.getAttribute(attr);
      if (original == null) continue;
      const translated = translateString(original);
      if (el.getAttribute(attr) !== translated)
        el.setAttribute(attr, translated);
    }
  }

  function translateSubtree(root) {
    if (!root) return;

    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
      acceptNode(node) {
        if (!node.nodeValue || !node.nodeValue.trim())
          return NodeFilter.FILTER_REJECT;
        if (shouldIgnoreNode(node)) return NodeFilter.FILTER_REJECT;
        return NodeFilter.FILTER_ACCEPT;
      },
    });

    let node;
    while ((node = walker.nextNode())) {
      translateTextNode(node);
    }

    if (root.nodeType === Node.ELEMENT_NODE) {
      translateElementAttributes(root);
      if (root.querySelectorAll) {
        root
          .querySelectorAll("[placeholder],[title],[aria-label],[alt],[value]")
          .forEach((el) => translateElementAttributes(el));
      }
    } else {
      document
        .querySelectorAll("[placeholder],[title],[aria-label],[alt],[value]")
        .forEach((el) => translateElementAttributes(el));
    }
  }

  function setHtmlLangAndDir(lang) {
    const html = document.documentElement;
    html.setAttribute("lang", lang);

    if (lang === "ar") {
      html.setAttribute("dir", "rtl");
      html.classList.add("lang-ar");
      html.classList.remove("lang-fr", "lang-en");
    } else {
      html.setAttribute("dir", "ltr");
      html.classList.remove("lang-ar");
      html.classList.add(lang === "fr" ? "lang-fr" : "lang-en");
      html.classList.remove(lang === "fr" ? "lang-en" : "lang-fr");
    }
  }

  function updateSwitcherActiveState() {
    // Update toggle label
    const label = document.querySelector(".lang-switcher .lang-current");
    if (label) {
      label.textContent = currentLang.toUpperCase();
    }

    // Update options state
    const btns = document.querySelectorAll(".lang-switcher .lang-option");
    btns.forEach((b) => {
      const isActive = b.dataset.lang === currentLang;
      b.classList.toggle("active", isActive);
      b.setAttribute("aria-pressed", isActive ? "true" : "false");
    });
  }

  function injectLanguageSwitcher() {
    if (document.querySelector(".lang-switcher")) return;

    const switcher = document.createElement("div");
    switcher.className = "lang-switcher dropdown-wrapper";
    switcher.setAttribute("data-i18n-ignore", "true");

    // Main Toggle Button
    const toggleBtn = document.createElement("button");
    toggleBtn.type = "button";
    toggleBtn.className = "lang-toggle";
    toggleBtn.setAttribute("aria-label", "Select language");
    toggleBtn.innerHTML = `
      <i class="fas fa-globe"></i>
      <span class="lang-current">${currentLang.toUpperCase()}</span>
      <i class="fas fa-chevron-down"></i>
    `;

    // Dropdown Menu
    const menu = document.createElement("div");
    menu.className = "lang-menu";

    const options = [
      { code: "en", label: "English" },
      { code: "fr", label: "Français" },
      { code: "ar", label: "العربية" },
    ];

    options.forEach((opt) => {
      const btn = document.createElement("button");
      btn.type = "button";
      btn.className = "lang-option";
      btn.dataset.lang = opt.code;
      btn.textContent = opt.label;
      if (opt.code === currentLang) btn.classList.add("active");

      btn.addEventListener("click", (e) => {
        e.stopPropagation(); // prevent bubbling to document click
        switcher.classList.remove("open");
        api.setLanguage(opt.code);
      });
      menu.appendChild(btn);
    });

    switcher.appendChild(toggleBtn);
    switcher.appendChild(menu);

    // Toggle Logic
    toggleBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      switcher.classList.toggle("open");
    });

    // Close on click outside
    document.addEventListener("click", (e) => {
      if (!switcher.contains(e.target)) {
        switcher.classList.remove("open");
      }
    });

    const navButtons =
      document.querySelector(".navbar .nav-buttons") ||
      document.querySelector(".header .nav-buttons") ||
      document.querySelector(".dashboard-actions");

    if (navButtons) {
      // Prepend to show before Login/Signup buttons
      navButtons.prepend(switcher);
      updateSwitcherActiveState();
      return;
    }

    // Admin pages: place switcher in the admin header controls (avoid fixed overlay)
    const adminUser = document.querySelector(".admin-header .admin-user");
    if (adminUser) {
      switcher.classList.add("lang-switcher-admin");
      const logoutBtn = adminUser.querySelector(".btn-logout");
      if (logoutBtn) {
        adminUser.insertBefore(switcher, logoutBtn);
      } else {
        adminUser.appendChild(switcher);
      }
      updateSwitcherActiveState();
      return;
    }

    switcher.classList.add("lang-switcher-fixed");
    document.body.appendChild(switcher);
    updateSwitcherActiveState();
  }

  function withSmoothTransition(fn) {
    const body = document.body;
    if (!body) {
      fn();
      return;
    }

    body.classList.add("i18n-ready");
    body.classList.add("i18n-switching");
    window.requestAnimationFrame(() => {
      try {
        fn();
      } finally {
        window.setTimeout(() => {
          body.classList.remove("i18n-switching");
        }, 180);
      }
    });
  }

  function startMutationObserver() {
    if (mutationObserver) return;

    mutationObserver = new MutationObserver((mutations) => {
      for (const m of mutations) {
        if (m.type === "childList") {
          m.addedNodes.forEach((n) => {
            if (n.nodeType === Node.TEXT_NODE) {
              translateTextNode(n);
            } else if (n.nodeType === Node.ELEMENT_NODE) {
              translateSubtree(n);
            }
          });
        } else if (m.type === "attributes") {
          if (m.target && m.target.nodeType === Node.ELEMENT_NODE) {
            translateElementAttributes(m.target);
          }
        }
      }
    });

    mutationObserver.observe(document.documentElement, {
      subtree: true,
      childList: true,
      attributes: true,
      attributeFilter: [
        "placeholder",
        "title",
        "aria-label",
        "alt",
        "value",
        "content",
      ],
    });
  }

  function getInitialLanguage() {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored && SUPPORTED.includes(stored)) return stored;
    const htmlLang = (
      document.documentElement.getAttribute("lang") || ""
    ).toLowerCase();
    if (SUPPORTED.includes(htmlLang)) return htmlLang;
    return "en";
  }

  const api = {
    t(key) {
      return translateString(key);
    },

    getLanguage() {
      return currentLang;
    },

    setLanguage(lang) {
      if (!SUPPORTED.includes(lang)) return;
      if (lang === currentLang) return;

      currentLang = lang;
      try {
        localStorage.setItem(STORAGE_KEY, lang);
      } catch (e) {
        // ignore
      }

      withSmoothTransition(() => {
        setHtmlLangAndDir(lang);
        translateSubtree(document.documentElement);
        updateSwitcherActiveState();
      });
    },

    refresh() {
      translateSubtree(document.documentElement);
    },

    init() {
      if (initialized) return;
      initialized = true;

      const run = () => {
        currentLang = getInitialLanguage();
        setHtmlLangAndDir(currentLang);
        injectLanguageSwitcher();
        translateSubtree(document.documentElement);
        updateSwitcherActiveState();
        startMutationObserver();
      };

      if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", run, { once: true });
      } else {
        run();
      }
    },
  };

  window.I18N = window.I18N || api;

  try {
    window.I18N.init();
  } catch (e) {
    // ignore
  }
})();

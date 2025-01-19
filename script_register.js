// Fonction de validation pour la page de connexion
function validateLoginForm() {
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();

    // Vérifier que l'email et le mot de passe sont remplis
    if (!email || !password) {
        alert("Veuillez remplir tous les champs.");
        return false;
    }

    // Vérifier que l'email est valide
    if (!validateEmail(email)) {
        alert("Veuillez entrer une adresse email valide.");
        return false;
    }

    return true; // Si tout est valide, le formulaire est soumis
}

// Fonction pour afficher/masquer le mot de passe
function togglePasswordVisibility(id) {
    const passwordField = document.getElementById(id);
    const icon = passwordField.nextElementSibling;
    if (passwordField.type === "password") {
        passwordField.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        passwordField.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}

// Fonction de validation du formulaire d'inscription
function validateForm() {
    const firstName = document.getElementById("first-name").value.trim();
    const lastName = document.getElementById("last-name").value.trim();
    const email = document.getElementById("email").value.trim();
    const phone = document.getElementById("phone").value.trim();
    const password = document.getElementById("password").value.trim();
    const confirmPassword = document.getElementById("confirm-password").value.trim();
    const terms = document.getElementById("terms").checked;

    // Vérifier que tous les champs sont remplis
    if (!firstName || !lastName || !email || !phone || !password || !confirmPassword) {
        alert("Veuillez remplir tous les champs.");
        return false;
    }

    // Vérifier que l'email est valide
    if (!validateEmail(email)) {
        alert("Veuillez entrer une adresse email valide.");
        return false;
    }

    // Vérifier que les mots de passe correspondent
    if (password !== confirmPassword) {
        alert("Les mots de passe ne correspondent pas.");
        return false;
    }

    alert("Compte créé avec succès !");
    return true; // Si tout est valide, le formulaire est soumis
}

// Fonction pour valider l'email
function validateEmail(email) {
    const re = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return re.test(email);
}

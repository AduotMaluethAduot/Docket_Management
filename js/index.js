document.addEventListener("DOMContentLoaded", () => {
    // Display a dynamic welcome message
    const welcomeMessage = document.getElementById("welcome-message");
    const welcomeSubtext = document.querySelector(".welcome p");
    const user = sessionStorage.getItem("username") || "Guest";

    const hours = new Date().getHours();
    let greeting;

    if (hours < 12) {
        greeting = "Good Morning";
    } else if (hours < 18) {
        greeting = "Good Afternoon";
    } else {
        greeting = "Good Evening";
    }

    // Update welcome message
    welcomeMessage.textContent = `${greeting}, ${user}! Welcome to Aduot Jok's Docket Management Web App!`;

    // Apply fade-in animation to welcome message
    welcomeMessage.style.opacity = 0;
    welcomeMessage.style.transform = "translateY(-20px)";
    welcomeMessage.style.transition = "opacity 1.5s ease, transform 1.5s ease";

    setTimeout(() => {
        welcomeMessage.style.opacity = 1;
        welcomeMessage.style.transform = "translateY(0)";
    }, 300);

    // Apply slide-in animation to subtext
    welcomeSubtext.style.opacity = 0;
    welcomeSubtext.style.transform = "translateX(-30px)";
    welcomeSubtext.style.transition = "opacity 1.5s ease, transform 1.5s ease";

    setTimeout(() => {
        welcomeSubtext.style.opacity = 1;
        welcomeSubtext.style.transform = "translateX(0)";
    }, 600);

    // Animate hero section
    const heroHeading = document.querySelector(".hero-text h2");
    const heroParagraph = document.querySelector(".hero-text p");
    const heroButton = document.querySelector(".hero-text .cta-btn");

    // Initial state for animation
    heroHeading.style.opacity = 0;
    heroHeading.style.transform = "scale(0.9)";
    heroHeading.style.transition = "opacity 0.8s ease, transform 0.8s ease";

    heroParagraph.style.opacity = 0;
    heroParagraph.style.transform = "translateY(20px)";
    heroParagraph.style.transition = "opacity 0.8s ease 0.2s, transform 0.8s ease 0.2s";

    heroButton.style.opacity = 0;
    heroButton.style.transform = "scale(0.9)";
    heroButton.style.transition = "opacity 0.8s ease 0.4s, transform 0.8s ease 0.4s";

    // Trigger the animations after a short delay
    setTimeout(() => {
        heroHeading.style.opacity = 1;
        heroHeading.style.transform = "scale(1)";
        heroParagraph.style.opacity = 1;
        heroParagraph.style.transform = "translateY(0)";
        heroButton.style.opacity = 1;
        heroButton.style.transform = "scale(1)";
    }, 300);

    // Add animations to feature cards on scroll
    const featureCards = document.querySelectorAll(".feature-cards .card");
    featureCards.forEach((card, index) => {
        card.style.opacity = 0;
        card.style.transform = "translateY(20px)";
        card.style.transition = `opacity 0.5s ease ${index * 0.2}s, transform 0.5s ease ${index * 0.2}s`;

        // Trigger animation on scroll
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        card.style.opacity = 1;
                        card.style.transform = "translateY(0)";
                    }
                });
            },
            { threshold: 0.1 }
        );

        observer.observe(card);
    });

    // Smooth scrolling for internal links
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener("click", function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute("href"));
            target.scrollIntoView({
                behavior: "smooth",
                block: "start",
            });
        });
    });

    // Add smooth hover effect to feature cards
    featureCards.forEach((card) => {
        card.addEventListener("mouseenter", () => {
            card.style.transform = "scale(1.05) translateY(-10px)";
            card.style.boxShadow = "0 8px 15px rgba(0, 0, 0, 0.2)";
            card.style.transition = "transform 0.3s ease, box-shadow 0.3s ease";
        });

        card.addEventListener("mouseleave", () => {
            card.style.transform = "scale(1) translateY(0)";
            card.style.boxShadow = "0 4px 6px rgba(0, 0, 0, 0.1)";
            card.style.transition = "transform 0.3s ease, box-shadow 0.3s ease";
        });
    });

    // Add hover effect to About Us and Contact Us sections
    const aboutSection = document.getElementById("about");
    const footer = document.getElementById("contact");

    // Hover effect for About Us
    aboutSection.addEventListener("mouseenter", () => {
        aboutSection.style.transform = "scale(1.05) translateY(-10px)";
        aboutSection.style.boxShadow = "0 8px 15px rgba(0, 0, 0, 0.2)";
        aboutSection.style.transition = "transform 0.3s ease, box-shadow 0.3s ease";
    });

    aboutSection.addEventListener("mouseleave", () => {
        aboutSection.style.transform = "scale(1) translateY(0)";
        aboutSection.style.boxShadow = "0 4px 6px rgba(0, 0, 0, 0.1)";
        aboutSection.style.transition = "transform 0.3s ease, box-shadow 0.3s ease";
    });

    // Hover effect for Contact Us
    footer.addEventListener("mouseenter", () => {
        footer.style.transform = "scale(1.05) translateY(-10px)";
        footer.style.boxShadow = "0 8px 15px rgba(0, 0, 0, 0.2)";
        footer.style.transition = "transform 0.3s ease, box-shadow 0.3s ease";
    });

    footer.addEventListener("mouseleave", () => {
        footer.style.transform = "scale(1) translateY(0)";
        footer.style.boxShadow = "0 4px 6px rgba(0, 0, 0, 0.1)";
        footer.style.transition = "transform 0.3s ease, box-shadow 0.3s ease";
    });

    // Add animations to About Us and Contact Us sections on scroll
    aboutSection.style.opacity = 0;
    aboutSection.style.transform = "translateY(20px)";
    aboutSection.style.transition = "opacity 0.5s ease, transform 0.5s ease";

    footer.style.opacity = 0;
    footer.style.transform = "translateY(20px)";
    footer.style.transition = "opacity 0.5s ease, transform 0.5s ease";

    const aboutObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    aboutSection.style.opacity = 1;
                    aboutSection.style.transform = "translateY(0)";
                }
            });
        },
        { threshold: 0.1 }
    );

    const contactObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    footer.style.opacity = 1;
                    footer.style.transform = "translateY(0)";
                }
            });
        },
        { threshold: 0.1 }
    );

    aboutObserver.observe(aboutSection);
    contactObserver.observe(footer);
});

document.addEventListener("DOMContentLoaded", () => {
    // Zoom on scroll for hero section
    const heroTitle = document.querySelector(".hero-title");
    const heroSubtitle = document.querySelector(".hero-subtitle");
    const heroActions = document.querySelector(".hero-actions");
    const heroSection = document.querySelector(".zoom-section");

    if (heroTitle && heroSubtitle) {
        window.addEventListener("scroll", () => {
            const scrollY = window.scrollY;

            if (scrollY < window.innerHeight) {
                // Apply zoom scaling based on scroll position
                // Slower scaling for text, faster for overall container
                const scale = 1 + scrollY * 0.0005;
                const opacity = 1 - scrollY * 0.0025;
                const translateY = scrollY * 0.35;

                heroTitle.style.transform = `scale(${scale}) translateY(${translateY}px)`;
                heroTitle.style.opacity = Math.max(0, opacity);

                heroSubtitle.style.transform = `translateY(${translateY * 1.1}px)`;
                heroSubtitle.style.opacity = Math.max(0, opacity - 0.1);

                if (heroActions) {
                    heroActions.style.transform = `translateY(${translateY * 0.8}px)`;
                    heroActions.style.opacity = Math.max(0, opacity - 0.3);
                }
            }
        });
    }

    // Navbar scroll effect
    const nav = document.querySelector(".glass-nav");
    if (nav) {
        window.addEventListener("scroll", () => {
            if (window.scrollY > 50) {
                nav.classList.add("bg-slate-950/80", "shadow-xl");
                nav.classList.remove("py-4");
                nav.classList.add("py-2");
            } else {
                nav.classList.remove("bg-slate-950/80", "shadow-xl");
                nav.classList.remove("py-2");
                nav.classList.add("py-4");
            }
        });
    }

    // Simple Intersection Observer for Bento Grid fade-in
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add(
                            "opacity-100",
                            "translate-y-0",
                        );
                        entry.target.classList.remove(
                            "opacity-0",
                            "translate-y-12",
                        );
                    }, index * 120);
                    observer.unobserve(entry.target);
                }
            });
        },
        {
            threshold: 0.1,
            rootMargin: "0px 0px -50px 0px",
        },
    );

    document
        .querySelectorAll(".bento-item, .section-header")
        .forEach((item) => {
            item.classList.add(
                "opacity-0",
                "translate-y-12",
                "transition-all",
                "duration-[800ms]",
                "ease-out",
            );
            observer.observe(item);
        });
});

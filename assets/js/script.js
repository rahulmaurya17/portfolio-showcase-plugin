document.addEventListener("DOMContentLoaded", function () {
    const filterButtons = document.querySelectorAll(".ps-filter-btn");
    const projectCards = document.querySelectorAll(".ps-portfolio-card");

    if (!filterButtons.length) return;

    filterButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const filterValue = this.getAttribute("data-filter");

            filterButtons.forEach(function (btn) {
                btn.classList.remove("active");
            });

            this.classList.add("active");

            projectCards.forEach(function (card) {
                const cardCategory = card.getAttribute("data-category");

                if (filterValue === "all" || filterValue === cardCategory) {
                    card.style.display = "block";
                } else {
                    card.style.display = "none";
                }
            });
        });
    });
});
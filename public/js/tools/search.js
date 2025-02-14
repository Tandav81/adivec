document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("searchInput");
    const resultsContainer = document.getElementById("autocompleteResults");

    input.addEventListener("input", function () {
        const query = input.value;
        if (query.length < 2) {
            resultsContainer.innerHTML = "";
            return;
        }

        fetch(`/search?q=${query}`)
            .then(response => response.json())
            .then(results => {
                resultsContainer.innerHTML = "";
                results.forEach(item => {
                    const li = document.createElement("li");
                    li.textContent = item.name;
                    li.setAttribute("data-url", item.url);
                    li.onclick = function () {
                        window.location.href = this.getAttribute("data-url");
                    };
                    resultsContainer.appendChild(li);
                });
            });
    });
});
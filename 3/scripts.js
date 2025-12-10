document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const table = document.getElementById('customerTable');
    const rows = table.querySelectorAll("tbody tr");

    // Hàm lọc bảng
    function filterTable() {
        const keyword = searchInput.value.toLowerCase();

        rows.forEach(row => {
            const name = row.cells[0].textContent.toLowerCase();
            const code = row.cells[1].textContent.toLowerCase();

            if (name.includes(keyword) || code.includes(keyword)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    // Hàm debounce
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                func.apply(context, args);
            }, wait);
        };
    }

    // Sử dụng debounce với thời gian chờ 300ms
    searchInput.addEventListener('input', debounce(filterTable, 300));
});
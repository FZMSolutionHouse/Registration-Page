
        function filterUsers() {
            const searchBox = document.getElementById('searchBox');
            const table = document.getElementById('usersTable');
            const rows = table.getElementsByTagName('tr');
            const filter = searchBox.value.toLowerCase();

            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < cells.length - 1; j++) { // -1 to skip actions column
                    if (cells[j].textContent.toLowerCase().includes(filter)) {
                        found = true;
                        break;
                    }
                }
                
                rows[i].style.display = found ? '' : 'none';
            }
        }

        // Auto-refresh every 30 seconds
        setInterval(function() {
            // Optional: You can add auto-refresh functionality here
            // location.reload();
        }, 30000);
    
// assets/js/customers.js

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('industry_search');
    const hiddenInput = document.getElementById('industry_id');
    const suggestionsDiv = document.getElementById('industry_suggestions');
    
    if (!searchInput || !suggestionsDiv) return;
    
    let timeout = null;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(timeout);
        const query = this.value.trim();
        
        if (query.length < 1) {
            suggestionsDiv.classList.remove('active');
            return;
        }
        
        timeout = setTimeout(() => {
            fetch(`api/industries.php?action=search&search=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    suggestionsDiv.innerHTML = '';
                    
                    // موارد موجود
                    if (data.length > 0) {
                        data.forEach(item => {
                            const div = document.createElement('div');
                            div.className = 'suggestion-item';
                            div.textContent = item.title;
                            div.addEventListener('click', () => {
                                searchInput.value = item.title;
                                hiddenInput.value = item.id;
                                suggestionsDiv.classList.remove('active');
                            });
                            suggestionsDiv.appendChild(div);
                        });
                    }
                    
                    // گزینه افزودن جدید
                    const addDiv = document.createElement('div');
                    addDiv.className = 'suggestion-item suggestion-add';
                    addDiv.textContent = `+ افزودن "${query}" جدید`;
                    addDiv.addEventListener('click', () => {
                        fetch(`api/industries.php?action=add&title=${encodeURIComponent(query)}`)
                            .then(res => res.json())
                            .then(result => {
                                if (result.id) {
                                    hiddenInput.value = result.id;
                                    searchInput.value = query;
                                    suggestionsDiv.classList.remove('active');
                                }
                            });
                    });
                    suggestionsDiv.appendChild(addDiv);
                    
                    suggestionsDiv.classList.add('active');
                });
        }, 300);
    });
    
    // بستن suggestions با کلیک بیرون
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
            suggestionsDiv.classList.remove('active');
        }
    });
    
    // اگر مقدار hidden پر بود (در حالت ویرایش)
    if (hiddenInput.value && !searchInput.value) {
        // هیچی - کاربر خودش میبینه
    }
});
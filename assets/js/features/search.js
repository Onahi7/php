export const initSearch = () => {
    class AutoComplete {
        constructor(input, options = {}) {
            this.input = input;
            this.options = {
                minChars: 2,
                debounceMs: 300,
                maxResults: 10,
                ...options
            };
            
            this.results = [];
            this.selectedIndex = -1;
            this.dropdownVisible = false;
            
            this.init();
        }
        
        init() {
            // Create results dropdown
            this.dropdown = document.createElement('div');
            this.dropdown.className = 'absolute w-full bg-white dark:bg-gray-800 shadow-lg rounded-md mt-1 z-50 hidden';
            this.input.parentNode.style.position = 'relative';
            this.input.parentNode.appendChild(this.dropdown);
            
            // Add event listeners
            this.input.addEventListener('input', this.debounce(this.handleInput.bind(this), this.options.debounceMs));
            this.input.addEventListener('keydown', this.handleKeydown.bind(this));
            document.addEventListener('click', this.handleClickOutside.bind(this));
            
            // Add loading indicator
            this.loadingIndicator = document.createElement('div');
            this.loadingIndicator.className = 'absolute right-3 top-1/2 transform -translate-y-1/2 hidden';
            this.loadingIndicator.innerHTML = `
                <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            `;
            this.input.parentNode.appendChild(this.loadingIndicator);
        }
        
        async handleInput(e) {
            const query = e.target.value.trim();
            
            if (query.length < this.options.minChars) {
                this.hideDropdown();
                return;
            }
            
            this.showLoading();
            
            try {
                const results = await this.fetchResults(query);
                this.results = results.slice(0, this.options.maxResults);
                this.renderDropdown();
            } catch (error) {
                console.error('Search error:', error);
            } finally {
                this.hideLoading();
            }
        }
        
        async fetchResults(query) {
            const response = await fetch(`/api/search?q=${encodeURIComponent(query)}`);
            if (!response.ok) throw new Error('Search failed');
            return await response.json();
        }
        
        renderDropdown() {
            if (!this.results.length) {
                this.hideDropdown();
                return;
            }
            
            this.dropdown.innerHTML = this.results.map((result, index) => `
                <div class="p-3 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer ${
                    index === this.selectedIndex ? 'bg-gray-100 dark:bg-gray-700' : ''
                }" data-index="${index}">
                    <div class="font-medium">${this.highlightMatch(result.title)}</div>
                    ${result.subtitle ? `<div class="text-sm text-gray-500">${this.highlightMatch(result.subtitle)}</div>` : ''}
                </div>
            `).join('');
            
            this.dropdown.querySelectorAll('div[data-index]').forEach(el => {
                el.addEventListener('click', () => this.handleResultClick(parseInt(el.dataset.index)));
            });
            
            this.showDropdown();
        }
        
        highlightMatch(text) {
            const query = this.input.value.trim();
            if (!query) return text;
            
            const regex = new RegExp(`(${query})`, 'gi');
            return text.replace(regex, '<mark class="bg-yellow-200 dark:bg-yellow-700">$1</mark>');
        }
        
        handleKeydown(e) {
            switch(e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    this.selectedIndex = Math.min(this.selectedIndex + 1, this.results.length - 1);
                    this.renderDropdown();
                    break;
                    
                case 'ArrowUp':
                    e.preventDefault();
                    this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                    this.renderDropdown();
                    break;
                    
                case 'Enter':
                    if (this.selectedIndex >= 0) {
                        e.preventDefault();
                        this.handleResultClick(this.selectedIndex);
                    }
                    break;
                    
                case 'Escape':
                    this.hideDropdown();
                    break;
            }
        }
        
        handleResultClick(index) {
            const result = this.results[index];
            this.input.value = result.title;
            this.hideDropdown();
            
            // Trigger custom event
            const event = new CustomEvent('resultSelected', { detail: result });
            this.input.dispatchEvent(event);
        }
        
        handleClickOutside(e) {
            if (!this.input.contains(e.target) && !this.dropdown.contains(e.target)) {
                this.hideDropdown();
            }
        }
        
        showDropdown() {
            this.dropdown.classList.remove('hidden');
            this.dropdownVisible = true;
        }
        
        hideDropdown() {
            this.dropdown.classList.add('hidden');
            this.dropdownVisible = false;
            this.selectedIndex = -1;
        }
        
        showLoading() {
            this.loadingIndicator.classList.remove('hidden');
        }
        
        hideLoading() {
            this.loadingIndicator.classList.add('hidden');
        }
        
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    }

    // Initialize autocomplete on search inputs
    document.querySelectorAll('input[data-search]').forEach(input => {
        new AutoComplete(input, {
            minChars: parseInt(input.dataset.minChars) || 2,
            debounceMs: parseInt(input.dataset.debounce) || 300,
            maxResults: parseInt(input.dataset.maxResults) || 10
        });
    });
};

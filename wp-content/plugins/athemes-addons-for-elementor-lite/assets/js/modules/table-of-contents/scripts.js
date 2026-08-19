(function ($) {

	var aThemesAddonsTableOfContents = function ($scope, $) {
        $(document).ready(function () {

            /**
             * Table of Contents Handler Class
             */
            class TableOfContentsHandler {
                constructor(element) {
                    this.$element = $(element);
                    this.$tocList = this.$element.find('.toc-list');
                    this.$tocContent = this.$element.find('.toc-content');
                    this.$toggleButton = this.$element.find('.toc-toggle-button');
                    
                    this.settings = this.getSettings();
                    this.headings = [];
                    this.tocItems = [];
                    
                    this.init();
                }

                /**
                 * Initialize the TOC functionality
                 */
                init() {
                    this.bindEvents();
                    this.generateTOC();
                    this.handleInitialState();
                    this.handleResponsiveCollapse();
                    this.initScrollSpy();
                }

                /**
                 * Get settings from data attributes
                 */
                                getSettings() {
                    const $iconTemplate = this.$element.find('.toc-custom-icon-template');
                    const customIcon = $iconTemplate.length > 0 ? $iconTemplate.html() : '';
                    
                    return {
                        headingTypes: (this.$element.data('heading-types') || 'h1,h2,h3,h4').split(','),
                        containerSelector: this.$element.data('container-selector') || '',
                        excludeClasses: this.$element.data('exclude-classes') || '',
                        maxNestingLevel: parseInt(this.$element.data('max-nesting-level')) || 3,
                        listStyle: this.$element.hasClass('toc-style-custom') ? 'custom' : 'numbers',
                        customIcon: customIcon,
                        enableCollapsible: this.$element.find('.toc-toggle-button').length > 0,
                        initiallyCollapsed: this.$element.hasClass('toc-collapsed')
                    };
                }

                /**
                 * Bind event handlers
                 */
                bindEvents() {
            // Toggle button click
            this.$toggleButton.on('click', this.toggleTOC.bind(this));
            
            // Toggle button keyboard support
            this.$toggleButton.on('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.toggleTOC();
                }
            });

            // Smooth scroll for TOC links
            this.$element.on('click', '.toc-list a[href^="#"]', this.handleSmoothScroll.bind(this));

            // Window resize for responsive behavior
            $(window).on('resize', this.debounce(this.handleResponsiveCollapse.bind(this), 250));

            // Scroll events for active heading highlighting
            $(window).on('scroll', this.throttle(this.updateActiveHeading.bind(this), 100));
        }

                /**
                 * Generate table of contents from page headings
                 */
                generateTOC() {
            this.$element.addClass('toc-loading');
            
            // Find container to search in
            const $container = this.getContainer();
            
            // Find headings
            this.findHeadings($container);
            
            // Generate TOC HTML
            if (this.headings.length > 0) {
                const tocHTML = this.buildTOCHTML();
                this.$tocList.html(tocHTML);
                this.$element.removeClass('toc-empty');
            } else {
                this.$element.addClass('toc-empty');
            }
            
            this.$element.removeClass('toc-loading');
        }

                /**
                 * Get the container element to search for headings
                 */
                getContainer() {
            if (this.settings.containerSelector) {
                let el = null;
                try {
                    el = document.querySelector(this.settings.containerSelector);
                } catch (err) {
                    el = null;
                }
                if (el) {
                    return $(el);
                }
            }
            
            // Fallback to common content containers
            const fallbacks = ['.entry-content', '.post-content', '.page-content', 'main', 'article', 'body'];
            for (let selector of fallbacks) {
                const $container = $(selector);
                if ($container.length > 0) {
                    return $container.first();
                }
            }
            
            return $(document);
        }

                /**
                 * Find and process headings in the container
                 */
                findHeadings($container) {
            const headingSelector = this.settings.headingTypes.join(',');
            const excludeClasses = this.settings.excludeClasses.split(',').map(cls => cls.trim()).filter(cls => cls);
            
            this.headings = [];
            
            $container.find(headingSelector).each((index, heading) => {
                const $heading = $(heading);
                
                // Skip if heading has exclude class
                if (excludeClasses.length > 0) {
                    const hasExcludeClass = excludeClasses.some(excludeClass => 
                        $heading.hasClass(excludeClass)
                    );
                    if (hasExcludeClass) {
                        return;
                    }
                }
                
                // Skip empty headings
                const text = $heading.text().trim();
                if (!text) {
                    return;
                }
                
                // Generate unique ID if not exists
                let id = $heading.attr('id');
                if (!id) {
                    id = this.generateHeadingId(text, index);
                    $heading.attr('id', id);
                }
                
                // Get heading level
                const tagName = heading.tagName.toLowerCase();
                const level = parseInt(tagName.substring(1));
                
                this.headings.push({
                    id: id,
                    text: text,
                    level: level,
                    element: $heading[0]
                });
            });
        }

                /**
                 * Generate unique ID for heading
                 */
                generateHeadingId(text, index) {
            // Clean text and create slug
            let slug = text.toLowerCase()
                .replace(/[^\w\s-]/g, '') // Remove special chars
                .replace(/\s+/g, '-')      // Replace spaces with hyphens
                .replace(/--+/g, '-')     // Replace multiple hyphens
                .replace(/^-|-$/g, '');   // Remove leading/trailing hyphens
            
            // Ensure uniqueness
            let id = slug || 'heading-' + (index + 1);
            let counter = 1;
            while (document.getElementById(id)) {
                id = slug + '-' + counter;
                counter++;
            }
            
            return id;
        }

                /**
                 * Build TOC HTML structure
                 */
                buildTOCHTML() {
            if (this.headings.length === 0) {
                return '';
            }

            const tocTree = this.buildTOCTree();
            return this.renderTOCTree(tocTree, 1);
        }

                /**
                 * Build hierarchical TOC tree structure
                 */
                buildTOCTree() {
            const tree = [];
            const stack = [];

            this.headings.forEach((heading, index) => {
                const item = {
                    ...heading,
                    number: '',
                    children: []
                };

                // Find appropriate parent level
                while (stack.length > 0 && stack[stack.length - 1].level >= heading.level) {
                    stack.pop();
                }

                if (stack.length === 0) {
                    // Top level item
                    tree.push(item);
                } else {
                    // Child item
                    const parent = stack[stack.length - 1];
                    if (parent.level < heading.level && parent.children.length < 100) { // Reasonable limit
                        parent.children.push(item);
                    } else {
                        // If nesting is too deep or parent level issues, add to top level
                        tree.push(item);
                    }
                }

                stack.push(item);
            });

            // Generate numbers
            this.generateNumbers(tree, []);
            
            return tree;
        }

                /**
                 * Generate numbering for TOC items
                 */
                generateNumbers(items, parentNumbers) {
            items.forEach((item, index) => {
                const currentNumbers = [...parentNumbers, index + 1];
                
                if (this.settings.listStyle === 'numbers') {
                    item.number = currentNumbers.join('.');
                }
                
                if (item.children.length > 0 && currentNumbers.length < this.settings.maxNestingLevel) {
                    this.generateNumbers(item.children, currentNumbers);
                }
            });
        }

                /**
                 * Render TOC tree as HTML
                 */
                renderTOCTree(items, currentLevel) {
            if (items.length === 0 || currentLevel > this.settings.maxNestingLevel) {
                return '';
            }

            let html = '<ul>';
            
            items.forEach(item => {
                let marker = '';
                if (this.settings.listStyle === 'numbers') {
                    marker = this.escapeHtml(item.number);
                } else if (this.settings.listStyle === 'custom' && this.settings.customIcon) {
                    marker = this.settings.customIcon;
                }
                
                html += '<li>';
                html += `<span class="toc-marker">${marker}</span>`;
                html += `<a href="#${this.escapeHtml(item.id)}" data-heading-id="${this.escapeHtml(item.id)}">`;
                html += this.escapeHtml(item.text);
                html += '</a>';
                
                // Add children if within nesting limit
                if (item.children.length > 0 && currentLevel < this.settings.maxNestingLevel) {
                    html += this.renderTOCTree(item.children, currentLevel + 1);
                }
                
                html += '</li>';
            });
            
            html += '</ul>';
            return html;
        }

                /**
                 * Handle initial collapsed/expanded state
                 */
                handleInitialState() {
            if (!this.settings.enableCollapsible) {
                return;
            }

            // Set initial state based on initially_collapsed setting and breakpoint
            const isInitiallyCollapsed = this.settings.initiallyCollapsed || this.shouldAutoCollapse();
            
            if (isInitiallyCollapsed) {
                this.$element.addClass('toc-collapsed').removeClass('toc-expanded');
                this.$toggleButton.attr('aria-expanded', 'false');
            } else {
                this.$element.removeClass('toc-collapsed').addClass('toc-expanded');
                this.$toggleButton.attr('aria-expanded', 'true');
            }
        }

                /**
                 * Check if TOC should auto-collapse based on breakpoint
                 */
                shouldAutoCollapse() {
            const windowWidth = $(window).width();
            
            // Tablet breakpoint - auto collapse on tablet and below
            if (this.$element.hasClass('toc-collapsible-tablet') && windowWidth <= 1024) {
                return true;
            }
            
            return false;
        }

                /**
                 * Handle responsive collapsible behavior
                 */
                handleResponsiveCollapse() {
            if (!this.settings.enableCollapsible) {
                return;
            }

            // For tablet breakpoint - auto collapse/expand based on screen size
            if (this.$element.hasClass('toc-collapsible-tablet')) {
                const windowWidth = $(window).width();
                
                if (windowWidth <= 1024) {
                    // On tablet/mobile - ensure we can toggle
                    if (!this.$element.hasClass('toc-collapsed') && !this.$element.hasClass('toc-expanded')) {
                        this.$element.addClass('toc-collapsed');
                        this.$toggleButton.attr('aria-expanded', 'false');
                    }
                } else {
                    // On desktop - always show content
                    this.$element.removeClass('toc-collapsed').addClass('toc-expanded');
                    this.$toggleButton.attr('aria-expanded', 'true');
                }
            }
        }

                /**
                 * Toggle TOC visibility
                 */
                toggleTOC() {
            const isCollapsed = this.$element.hasClass('toc-collapsed');
            
            if (isCollapsed) {
                this.$element.removeClass('toc-collapsed').addClass('toc-expanded');
                this.$toggleButton.attr('aria-expanded', 'true');
            } else {
                this.$element.addClass('toc-collapsed').removeClass('toc-expanded');
                this.$toggleButton.attr('aria-expanded', 'false');
            }
        }

                /**
                 * Handle TOC link clicks
                 */
                handleSmoothScroll(e) {
            // Let the browser handle the smooth scrolling via CSS
            // Just update the active state
            const $link = $(e.currentTarget);
            const targetId = $link.attr('href').substring(1);
            
            // Update active state immediately
            this.setActiveHeading(targetId);
        }

                /**
                 * Initialize scroll spy functionality
                 */
                initScrollSpy() {
            this.updateActiveHeading();
        }

                /**
                 * Update active heading based on scroll position
                 */
                updateActiveHeading() {
            if (this.headings.length === 0) {
                return;
            }

            const scrollTop = $(window).scrollTop();
            
            // Simple offset calculation for fixed elements
            let offsetTop = 50; // Base buffer
            
            // Check for WordPress admin bar
            if ($('#wpadminbar').length > 0) {
                offsetTop += $('#wpadminbar').outerHeight() || 32;
            }
            
            let activeHeading = null;

            // Find the heading that's currently in view
            for (let i = this.headings.length - 1; i >= 0; i--) {
                const heading = this.headings[i];
                const $headingEl = $(heading.element);
                
                if ($headingEl.length > 0) {
                    const headingTop = $headingEl.offset().top;
                    if (scrollTop + offsetTop >= headingTop) {
                        activeHeading = heading.id;
                        break;
                    }
                }
            }

            this.setActiveHeading(activeHeading);
        }

                /**
                 * Set active heading in TOC
                 */
                setActiveHeading(headingId) {
            // Remove previous active states
            this.$tocList.find('a').removeClass('toc-active');
            
            // Add active state to current heading
            if (headingId) {
                this.$tocList.find(`a[data-heading-id="${headingId}"]`).addClass('toc-active');
            }
        }

                /**
                 * Utility: Escape HTML
                 */
                escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

                /**
                 * Utility: Debounce function
                 */
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

                /**
                 * Utility: Throttle function
                 */
                throttle(func, limit) {
                    let inThrottle;
                    return function executedFunction(...args) {
                        if (!inThrottle) {
                            func.apply(this, args);
                            inThrottle = true;
                            setTimeout(() => inThrottle = false, limit);
                        }
                    };
                }
            }

            /**
             * Initialize Table of Contents widgets
             */
            $scope.find('.athemes-addons-table-of-contents').each(function() {
                if (!$(this).data('toc-initialized')) {
                    new TableOfContentsHandler(this);
                    $(this).data('toc-initialized', true);
                }
            });

        }); 
	};

	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction('frontend/element_ready/athemes-addons-table-of-contents.default', aThemesAddonsTableOfContents );
	});

})(jQuery);

/**
 * JavaScript untuk Awalan Design.id
 * Jasa Desain Grafis Profesional untuk UMKM
 */

// Tunggu DOM selesai dimuat
document.addEventListener('DOMContentLoaded', function() {
    console.log('Awalan Design.id - JavaScript loaded');
    
    // ===== GLOBAL VARIABLES =====
    const body = document.body;
    const header = document.getElementById('header');
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('navMenu');
    const navLinks = document.querySelectorAll('.nav-menu a');
    const backToTop = document.getElementById('backToTop');
    const currentYear = document.getElementById('currentYear');
    
    // ===== MOBILE NAVIGATION =====
    function initMobileNavigation() {
        if (!hamburger || !navMenu) return;
        
        // Toggle mobile menu
        hamburger.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            hamburger.classList.toggle('active');
            body.classList.toggle('no-scroll');
            
            // Update hamburger icon
            const icon = hamburger.querySelector('i');
            if (navMenu.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
        
        // Close mobile menu when clicking on a link
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                navMenu.classList.remove('active');
                hamburger.classList.remove('active');
                body.classList.remove('no-scroll');
                
                // Update hamburger icon
                const icon = hamburger.querySelector('i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            });
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!navMenu.contains(event.target) && 
                !hamburger.contains(event.target) && 
                navMenu.classList.contains('active')) {
                navMenu.classList.remove('active');
                hamburger.classList.remove('active');
                body.classList.remove('no-scroll');
                
                // Update hamburger icon
                const icon = hamburger.querySelector('i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    }
    
    // ===== HEADER SCROLL EFFECT =====
    function initHeaderScroll() {
        if (!header) return;
        
        let lastScroll = 0;
        const scrollThreshold = 100;
        
        window.addEventListener('scroll', function() {
            const currentScroll = window.pageYOffset;
            
            // Add/remove scrolled class
            if (currentScroll > scrollThreshold) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            
            // Hide/show header on scroll
            if (currentScroll > lastScroll && currentScroll > 200) {
                // Scrolling down
                header.style.transform = 'translateY(-100%)';
            } else {
                // Scrolling up
                header.style.transform = 'translateY(0)';
            }
            
            lastScroll = currentScroll;
            
            // Update active nav link
            updateActiveNavLink();
        });
    }
    
    // ===== ACTIVE NAV LINK ON SCROLL =====
    function updateActiveNavLink() {
        const scrollPosition = window.scrollY + 100;
        const sections = document.querySelectorAll('section[id]');
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            const sectionId = section.getAttribute('id');
            
            if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === `#${sectionId}`) {
                        link.classList.add('active');
                    }
                });
            }
        });
    }
    
    // ===== BACK TO TOP BUTTON =====
    function initBackToTop() {
        if (!backToTop) return;
        
        // Show/hide button on scroll
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTop.classList.add('visible');
            } else {
                backToTop.classList.remove('visible');
            }
        });
        
        // Scroll to top on click
        backToTop.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // ===== PORTFOLIO FILTER =====
    function initPortfolioFilter() {
        const filterButtons = document.querySelectorAll('.filter-btn');
        const portfolioGrid = document.querySelector('.portfolio-grid');
        
        if (!filterButtons.length || !portfolioGrid) return;
        
        // Portfolio data
        const portfolioItems = [
            {
                id: 1,
                category: 'logo',
                title: 'Logo UMKM Kuliner',
                description: 'Desain logo untuk usaha makanan tradisional dengan sentuhan modern',
                color: '#FF6B6B',
                icon: 'pen-nib'
            },
            {
                id: 2,
                category: 'logo',
                title: 'Logo Startup Teknologi',
                description: 'Logo futuristic untuk perusahaan teknologi lokal',
                color: '#4ECDC4',
                icon: 'lightbulb'
            },
            {
                id: 3,
                category: 'branding',
                title: 'Branding Kopi Shop',
                description: 'Full branding package untuk café lokal',
                color: '#FFD166',
                icon: 'mug-hot'
            },
            {
                id: 4,
                category: 'marketing',
                title: 'Brosur Promosi UMKM',
                description: 'Brosur A4 untuk promosi produk lokal',
                color: '#06D6A0',
                icon: 'newspaper'
            },
            {
                id: 5,
                category: 'social-media',
                title: 'Social Media Kit Fashion',
                description: '15 konten Instagram untuk brand fashion lokal',
                color: '#118AB2',
                icon: 'hashtag'
            },
            {
                id: 6,
                category: 'social-media',
                title: 'Facebook Ads Banner',
                description: 'Banner iklan untuk kampanye Facebook Ads',
                color: '#EF476F',
                icon: 'bullhorn'
            },
            {
                id: 7,
                category: 'apparel',
                title: 'Desain Kaos Komunitas',
                description: 'Merchandise kaos untuk komunitas bisnis',
                color: '#073B4C',
                icon: 'tshirt'
            },
            {
                id: 8,
                category: 'marketing',
                title: 'Company Profile',
                description: 'Buku company profile 12 halaman',
                color: '#7209B7',
                icon: 'book-open'
            },
            {
                id: 9,
                category: 'apparel',
                title: 'Desain Jaket Brand',
                description: 'Jaket custom untuk merchandise brand',
                color: '#F3722C',
                icon: 'vest'
            }
        ];
        
        // Function to get category name
        function getCategoryName(category) {
            const names = {
                'logo': 'Logo',
                'branding': 'Branding',
                'marketing': 'Marketing',
                'social-media': 'Sosial Media',
                'apparel': 'Apparel'
            };
            return names[category] || 'Kategori';
        }
        
        // Function to display portfolio items
        function displayPortfolioItems(filter = 'all') {
            portfolioGrid.innerHTML = '';
            
            const filteredItems = filter === 'all' 
                ? portfolioItems 
                : portfolioItems.filter(item => item.category === filter);
            
            // Create portfolio items
            filteredItems.forEach(item => {
                const portfolioItem = document.createElement('div');
                portfolioItem.className = 'portfolio-item animate-fade-in-up';
                portfolioItem.setAttribute('data-category', item.category);
                
                portfolioItem.innerHTML = `
                    <div class="portfolio-img" style="background: linear-gradient(135deg, ${item.color} 0%, ${item.color}80 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                        <i class="fas fa-${item.icon}"></i>
                    </div>
                    <div class="portfolio-info">
                        <span class="portfolio-category">${getCategoryName(item.category)}</span>
                        <h3>${item.title}</h3>
                        <p>${item.description}</p>
                        <a href="#" class="btn btn-sm btn-outline mt-3" style="width: 100%;">
                            <i class="fas fa-eye"></i> Lihat Detail
                        </a>
                    </div>
                `;
                
                // Add click event to view details
                const viewBtn = portfolioItem.querySelector('.btn');
                viewBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    showPortfolioDetail(item);
                });
                
                portfolioGrid.appendChild(portfolioItem);
            });
            
            // Animate items
            animateOnScroll();
        }
        
        // Initialize with all items
        displayPortfolioItems();
        
        // Add click events to filter buttons
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Filter portfolio items
                const filterValue = this.getAttribute('data-filter');
                displayPortfolioItems(filterValue);
            });
        });
        
        // Portfolio detail modal
        function showPortfolioDetail(item) {
            // Create modal if it doesn't exist
            let modal = document.getElementById('portfolioModal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'portfolioModal';
                modal.className = 'portfolio-modal';
                modal.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.8);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 9999;
                    opacity: 0;
                    visibility: hidden;
                    transition: all 0.3s ease;
                `;
                
                modal.innerHTML = `
                    <div class="modal-content" style="background: white; border-radius: 12px; max-width: 800px; width: 90%; max-height: 90vh; overflow-y: auto; position: relative;">
                        <button class="modal-close" style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.5rem; color: #666; cursor: pointer; z-index: 10;">
                            <i class="fas fa-times"></i>
                        </button>
                        <div class="modal-body" style="padding: 2rem;"></div>
                    </div>
                `;
                
                document.body.appendChild(modal);
                
                // Close modal on close button click
                const closeBtn = modal.querySelector('.modal-close');
                closeBtn.addEventListener('click', function() {
                    modal.style.opacity = '0';
                    modal.style.visibility = 'hidden';
                    body.classList.remove('no-scroll');
                });
                
                // Close modal on outside click
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        modal.style.opacity = '0';
                        modal.style.visibility = 'hidden';
                        body.classList.remove('no-scroll');
                    }
                });
            }
            
            // Update modal content
            const modalBody = modal.querySelector('.modal-body');
            modalBody.innerHTML = `
                <div style="background: linear-gradient(135deg, ${item.color} 0%, ${item.color}80 100%); height: 200px; border-radius: 12px 12px 0 0; display: flex; align-items: center; justify-content: center; color: white; font-size: 4rem;">
                    <i class="fas fa-${item.icon}"></i>
                </div>
                <div style="padding: 2rem;">
                    <span class="portfolio-category">${getCategoryName(item.category)}</span>
                    <h2 style="margin: 1rem 0;">${item.title}</h2>
                    <p style="font-size: 1.1rem; line-height: 1.6; margin-bottom: 1.5rem;">${item.description}</p>
                    
                    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin: 2rem 0;">
                        <h4 style="margin-bottom: 1rem;">Detail Proyek</h4>
                        <ul style="list-style: none; margin: 0; padding: 0;">
                            <li style="padding: 0.5rem 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between;">
                                <span>Kategori:</span>
                                <span style="font-weight: 600;">${getCategoryName(item.category)}</span>
                            </li>
                            <li style="padding: 0.5rem 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between;">
                                <span>Klien:</span>
                                <span style="font-weight: 600;">UMKM Lokal</span>
                            </li>
                            <li style="padding: 0.5rem 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between;">
                                <span>Waktu Pengerjaan:</span>
                                <span style="font-weight: 600;">5-7 Hari</span>
                            </li>
                            <li style="padding: 0.5rem 0; display: flex; justify-content: space-between;">
                                <span>Status:</span>
                                <span style="font-weight: 600; color: #10b981;">Selesai</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="text-center">
                        <a href="https://wa.me/6285815056990?text=Saya%20tertarik%20dengan%20portofolio%20${encodeURIComponent(item.title)}" 
                           class="btn btn-primary" target="_blank" style="margin-right: 1rem;">
                            <i class="fab fa-whatsapp"></i> Konsultasi Proyek Serupa
                        </a>
                        <button class="btn btn-outline modal-close">
                            Tutup
                        </button>
                    </div>
                </div>
            `;
            
            // Add close event to new close button
            const newCloseBtn = modalBody.querySelector('.modal-close');
            newCloseBtn.addEventListener('click', function() {
                modal.style.opacity = '0';
                modal.style.visibility = 'hidden';
                body.classList.remove('no-scroll');
            });
            
            // Show modal
            modal.style.opacity = '1';
            modal.style.visibility = 'visible';
            body.classList.add('no-scroll');
        }
    }
    
    // ===== PRICING TABS =====
    function initPricingTabs() {
        const pricingTabs = document.querySelectorAll('.pricing-tab');
        const pricingContents = document.querySelectorAll('.pricing-content');
        
        if (!pricingTabs.length || !pricingContents.length) return;
        
        // Show initial content
        const initialTab = document.querySelector('.pricing-tab.active');
        if (initialTab) {
            const tabId = initialTab.getAttribute('data-tab');
            document.getElementById(`${tabId}-packages`).style.display = 'block';
        }
        
        // Add click events to tabs
        pricingTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // Remove active class from all tabs
                pricingTabs.forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Hide all pricing contents
                pricingContents.forEach(content => {
                    content.style.display = 'none';
                    content.classList.remove('animate-fade-in');
                });
                
                // Show selected pricing content
                const selectedContent = document.getElementById(`${tabId}-packages`);
                selectedContent.style.display = 'block';
                setTimeout(() => {
                    selectedContent.classList.add('animate-fade-in');
                }, 10);
            });
        });
        
        // Add animation to pricing cards
        const pricingCards = document.querySelectorAll('.pricing-card');
        pricingCards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });
    }
    
    // ===== TESTIMONIAL SLIDER =====
    function initTestimonialSlider() {
        const testimonialSlider = document.querySelector('.testimonial-slider');
        if (!testimonialSlider) return;
        
        // Testimonials data
        const testimonials = [
            {
                name: 'Budi Santoso',
                role: 'Pemilik UMKM Makanan "Rasa Nusantara"',
                content: 'Logo yang dibuat oleh Awalan Design.id sangat merepresentasikan bisnis saya. Hasilnya profesional dan proses pengerjaannya cepat. Pelanggan sekarang lebih mudah mengingat brand saya!',
                rating: 5,
                avatarColor: '#FF6B6B'
            },
            {
                name: 'Sari Dewi',
                role: 'Mahasiswa & Pengusaha Startup',
                content: 'Sebagai mahasiswa yang baru memulai bisnis, budget terbatas. Tapi tim Awalan Design.id memberikan hasil yang luar biasa dengan harga yang terjangkau. Desain brosur dan sosial media saya sekarang sangat menarik!',
                rating: 5,
                avatarColor: '#4ECDC4'
            },
            {
                name: 'Agus Wijaya',
                role: 'Pemilik Toko Online "Gadget Murah"',
                content: '10 tahun pengalaman benar-benar terasa. Desain yang diberikan tidak hanya bagus tapi juga strategis untuk pemasaran. Penjualan meningkat 40% setelah menggunakan jasa mereka.',
                rating: 5,
                avatarColor: '#118AB2'
            },
            {
                name: 'Linda Putri',
                role: 'Manajer Event Kampus',
                content: 'Desain sertifikat dan materi promosi untuk acara kampus kami sangat elegan dan profesional. Timnya responsif dan mudah diajak berkomunikasi. Hasilnya melebihi ekspektasi!',
                rating: 5,
                avatarColor: '#EF476F'
            },
            {
                name: 'Rendra Pratama',
                role: 'Pemilik Café "Kopi Teman"',
                content: 'Desain kaos merchandise untuk café saya sangat kreatif! Pelanggan banyak yang suka dan ingin membeli. Terima kasih Awalan Design.id!',
                rating: 5,
                avatarColor: '#073B4C'
            },
            {
                name: 'Dewi Anggraeni',
                role: 'Pemilik Butik Fashion',
                content: 'Social media kit yang dibuat sangat cocok dengan brand saya. Engagement di Instagram meningkat drastis sejak menggunakan desain dari Awalan Design.id.',
                rating: 5,
                avatarColor: '#7209B7'
            }
        ];
        
        // Create slider if Swiper is available
        if (typeof Swiper !== 'undefined') {
            const swiperWrapper = document.querySelector('.swiper-wrapper');
            
            // Clear existing content
            swiperWrapper.innerHTML = '';
            
            // Add testimonial slides
            testimonials.forEach(testimonial => {
                const slide = document.createElement('div');
                slide.className = 'swiper-slide';
                
                // Create rating stars
                let stars = '';
                for (let i = 0; i < testimonial.rating; i++) {
                    stars += '<i class="fas fa-star"></i>';
                }
                
                slide.innerHTML = `
                    <div class="testimonial-card">
                        <div class="testimonial-content">
                            <p>${testimonial.content}</p>
                        </div>
                        <div class="rating">
                            ${stars}
                        </div>
                        <div class="testimonial-author">
                            <div style="width: 60px; height: 60px; background-color: ${testimonial.avatarColor}; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.5rem;">
                                ${testimonial.name.charAt(0)}
                            </div>
                            <div class="testimonial-info">
                                <h4>${testimonial.name}</h4>
                                <p>${testimonial.role}</p>
                            </div>
                        </div>
                    </div>
                `;
                
                swiperWrapper.appendChild(slide);
            });
            
            // Initialize Swiper
            const swiper = new Swiper('.swiper-container', {
                slidesPerView: 1,
                spaceBetween: 20,
                loop: true,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                    dynamicBullets: true,
                },
                breakpoints: {
                    640: {
                        slidesPerView: 1,
                        spaceBetween: 20,
                    },
                    768: {
                        slidesPerView: 2,
                        spaceBetween: 30,
                    },
                    1024: {
                        slidesPerView: 3,
                        spaceBetween: 30,
                    },
                },
                on: {
                    init: function() {
                        console.log('Testimonial slider initialized');
                    }
                }
            });
            
            // Add pause on hover
            const swiperContainer = document.querySelector('.swiper-container');
            swiperContainer.addEventListener('mouseenter', function() {
                swiper.autoplay.stop();
            });
            
            swiperContainer.addEventListener('mouseleave', function() {
                swiper.autoplay.start();
            });
        } else {
            // Fallback if Swiper not loaded
            console.warn('Swiper not loaded, using fallback layout');
            
            const sliderContainer = document.querySelector('.testimonial-slider');
            const fallbackHTML = `
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                    ${testimonials.map(testimonial => {
                        let stars = '';
                        for (let i = 0; i < testimonial.rating; i++) {
                            stars += '<i class="fas fa-star"></i>';
                        }
                        
                        return `
                            <div class="testimonial-card">
                                <div class="testimonial-content">
                                    <p>${testimonial.content}</p>
                                </div>
                                <div class="rating">
                                    ${stars}
                                </div>
                                <div class="testimonial-author">
                                    <div style="width: 60px; height: 60px; background-color: ${testimonial.avatarColor}; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.5rem;">
                                        ${testimonial.name.charAt(0)}
                                    </div>
                                    <div class="testimonial-info">
                                        <h4>${testimonial.name}</h4>
                                        <p>${testimonial.role}</p>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            `;
            
            sliderContainer.innerHTML = fallbackHTML;
        }
    }
    
    // ===== CONTACT FORM =====
    function initContactForm() {
        const contactForm = document.getElementById('contactForm');
        if (!contactForm) return;
        
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const service = document.getElementById('service').value;
            const message = document.getElementById('message').value.trim();
            
            // Validate form
            if (!name || !phone || !service || !message) {
                showNotification('Harap isi semua field yang wajib diisi!', 'error');
                return;
            }
            
            // Service names mapping
            const serviceNames = {
                'paket-starter': 'Paket Starter',
                'paket-umkm': 'Paket UMKM Premium',
                'paket-business': 'Paket Business Growth',
                'paket-mahasiswa': 'Paket Mahasiswa',
                'logo-only': 'Logo Only',
                'social-media': 'Social Media Kit',
                'brosur': 'Brosur/Flyer',
                'lainnya': 'Lainnya'
            };
            
            // Create WhatsApp message
            const whatsappMessage = `Halo Awalan Design.id! 👋

Saya ${name}, tertarik dengan layanan desain grafis Anda.

📋 Detail Permintaan:
• Nama: ${name}
• Email: ${email || 'Tidak diisi'}
• WhatsApp: ${phone}
• Layanan: ${serviceNames[service] || service}
• Pesan: ${message}

Mohon info lebih lanjut mengenai layanan Anda. Terima kasih! 😊`;
            
            // Encode message for URL
            const encodedMessage = encodeURIComponent(whatsappMessage);
            
            // Create WhatsApp URL
            const whatsappURL = `https://wa.me/6285815056990?text=${encodedMessage}`;
            
            // Show success message
            showNotification('Mengarahkan ke WhatsApp...', 'success');
            
            // Open WhatsApp in new tab after short delay
            setTimeout(() => {
                window.open(whatsappURL, '_blank');
            }, 1000);
            
            // Reset form
            contactForm.reset();
            
            // Log to console (for debugging)
            console.log('Form submitted:', { name, email, phone, service, message });
        });
        
        // Form validation styles
        const formInputs = contactForm.querySelectorAll('.form-control[required]');
        formInputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                clearFieldError(this);
            });
        });
        
        // Field validation function
        function validateField(field) {
            const value = field.value.trim();
            const fieldName = field.previousElementSibling?.textContent || 'Field';
            
            if (!value) {
                showFieldError(field, `${fieldName} harus diisi`);
                return false;
            }
            
            if (field.type === 'email' && value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    showFieldError(field, 'Format email tidak valid');
                    return false;
                }
            }
            
            if (field.id === 'phone' && value) {
                const phoneRegex = /^[0-9+\-\s()]{10,}$/;
                if (!phoneRegex.test(value)) {
                    showFieldError(field, 'Format nomor telepon tidak valid');
                    return false;
                }
            }
            
            clearFieldError(field);
            return true;
        }
        
        // Show field error
        function showFieldError(field, message) {
            clearFieldError(field);
            
            field.style.borderColor = '#ef4444';
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error';
            errorDiv.style.cssText = `
                color: #ef4444;
                font-size: 0.875rem;
                margin-top: 0.25rem;
            `;
            errorDiv.textContent = message;
            
            field.parentNode.appendChild(errorDiv);
        }
        
        // Clear field error
        function clearFieldError(field) {
            field.style.borderColor = '';
            
            const existingError = field.parentNode.querySelector('.field-error');
            if (existingError) {
                existingError.remove();
            }
        }
    }
    
    // ===== NOTIFICATION SYSTEM =====
    function showNotification(message, type = 'info') {
        // Remove existing notification
        const existingNotification = document.querySelector('.notification');
        if (existingNotification) {
            existingNotification.remove();
        }
        
        // Create notification
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        // Set icon based on type
        let icon = 'info-circle';
        let bgColor = '#3b82f6';
        
        switch (type) {
            case 'success':
                icon = 'check-circle';
                bgColor = '#10b981';
                break;
            case 'error':
                icon = 'exclamation-circle';
                bgColor = '#ef4444';
                break;
            case 'warning':
                icon = 'exclamation-triangle';
                bgColor = '#f59e0b';
                break;
        }
        
        notification.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            background: ${bgColor};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            z-index: 10000;
            animation: slideInRight 0.3s ease-out;
            max-width: 400px;
        `;
        
        notification.innerHTML = `
            <i class="fas fa-${icon}" style="font-size: 1.25rem;"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease-out forwards';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }, 5000);
        
        // Add close on click
        notification.addEventListener('click', function() {
            notification.style.animation = 'slideOutRight 0.3s ease-out forwards';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        });
    }
    
    // ===== SMOOTH SCROLL =====
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href');
                
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    e.preventDefault();
                    
                    // Calculate offset (considering fixed header)
                    const headerHeight = header.offsetHeight;
                    const targetPosition = targetElement.offsetTop - headerHeight;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }
    
    // ===== ANIMATION ON SCROLL =====
    function animateOnScroll() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    
                    // Add specific animations based on element
                    if (entry.target.classList.contains('service-card')) {
                        entry.target.style.animation = 'fadeInUp 0.6s ease-out forwards';
                    }
                    
                    if (entry.target.classList.contains('pricing-card')) {
                        entry.target.style.animation = 'fadeInUp 0.6s ease-out forwards';
                    }
                    
                    if (entry.target.classList.contains('portfolio-item')) {
                        entry.target.style.animation = 'fadeInUp 0.6s ease-out forwards';
                    }
                    
                    // Stop observing after animation
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        // Observe elements
        const animatedElements = document.querySelectorAll(
            '.service-card, .portfolio-item, .pricing-card, .contact-item, .hero-stats .stat-item'
        );
        
        animatedElements.forEach((el, index) => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            el.style.transitionDelay = `${index * 0.1}s`;
            
            observer.observe(el);
        });
    }
    
    // ===== STATS COUNTER =====
    function initStatsCounter() {
        const statNumbers = document.querySelectorAll('.stat-number');
        
        if (!statNumbers.length) return;
        
        const observerOptions = {
            threshold: 0.5
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const statNumber = entry.target;
                    const target = parseInt(statNumber.textContent.replace('+', ''));
                    
                    animateCounter(statNumber, target, 2000);
                    observer.unobserve(statNumber);
                }
            });
        }, observerOptions);
        
        statNumbers.forEach(stat => {
            observer.observe(stat);
        });
        
        // Counter animation function
        function animateCounter(element, target, duration) {
            let start = 0;
            const increment = target / (duration / 16);
            const timer = setInterval(() => {
                start += increment;
                if (start >= target) {
                    element.textContent = target + '+';
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(start) + '+';
                }
            }, 16);
        }
    }
    
    // ===== CURRENT YEAR =====
    function updateCurrentYear() {
        if (currentYear) {
            currentYear.textContent = new Date().getFullYear();
        }
    }
    
    // ===== LAZY LOAD IMAGES =====
    function initLazyLoad() {
        const images = document.querySelectorAll('img[data-src]');
        
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            images.forEach(img => imageObserver.observe(img));
        } else {
            // Fallback for older browsers
            images.forEach(img => {
                img.src = img.dataset.src;
            });
        }
    }
    
    // ===== PRELOADER =====
    function initPreloader() {
        const preloader = document.getElementById('preloader');
        
        if (preloader) {
            // Remove preloader after page loads
            window.addEventListener('load', function() {
                setTimeout(() => {
                    preloader.style.opacity = '0';
                    preloader.style.visibility = 'hidden';
                    
                    setTimeout(() => {
                        if (preloader.parentNode) {
                            preloader.remove();
                        }
                    }, 500);
                }, 500);
            });
        }
    }
    
    // ===== THEME TOGGLE (Optional) =====
    function initThemeToggle() {
        const themeToggle = document.getElementById('themeToggle');
        
        if (themeToggle) {
            // Check for saved theme
            const savedTheme = localStorage.getItem('theme') || 'light';
            
            // Apply saved theme
            if (savedTheme === 'dark') {
                document.body.classList.add('dark-theme');
                themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            }
            
            // Toggle theme
            themeToggle.addEventListener('click', function() {
                document.body.classList.toggle('dark-theme');
                
                if (document.body.classList.contains('dark-theme')) {
                    localStorage.setItem('theme', 'dark');
                    themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
                } else {
                    localStorage.setItem('theme', 'light');
                    themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
                }
            });
        }
    }
    
    // ===== WHATSAPP FLOATING BUTTON =====
    function initWhatsAppButton() {
        // Create WhatsApp floating button
        const whatsappBtn = document.createElement('a');
        whatsappBtn.href = 'https://wa.me/6285815056990';
        whatsappBtn.target = '_blank';
        whatsappBtn.className = 'whatsapp-float';
        whatsappBtn.title = 'Chat via WhatsApp';
        
        whatsappBtn.style.cssText = `
            position: fixed;
            bottom: 100px;
            right: 20px;
            width: 60px;
            height: 60px;
            background-color: #25D366;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            z-index: 999;
            transition: all 0.3s ease;
            animation: float 3s ease-in-out infinite;
            text-decoration: none;
        `;
        
        whatsappBtn.innerHTML = '<i class="fab fa-whatsapp"></i>';
        
        document.body.appendChild(whatsappBtn);
        
        // Add hover effect
        whatsappBtn.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1)';
            this.style.boxShadow = '0 15px 30px rgba(0,0,0,0.3)';
        });
        
        whatsappBtn.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
            this.style.boxShadow = '0 10px 25px rgba(0,0,0,0.2)';
        });
        
        // Add pulse animation
        setInterval(() => {
            whatsappBtn.classList.add('pulse');
            setTimeout(() => {
                whatsappBtn.classList.remove('pulse');
            }, 1000);
        }, 5000);
    }
    
    // ===== KEYBOARD SHORTCUTS =====
    function initKeyboardShortcuts() {
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + / to focus search (if exists)
            if ((e.ctrlKey || e.metaKey) && e.key === '/') {
                e.preventDefault();
                const searchInput = document.querySelector('input[type="search"]');
                if (searchInput) {
                    searchInput.focus();
                }
            }
            
            // Escape to close modal
            if (e.key === 'Escape') {
                const modal = document.querySelector('.portfolio-modal');
                if (modal && modal.style.visibility === 'visible') {
                    modal.style.opacity = '0';
                    modal.style.visibility = 'hidden';
                    body.classList.remove('no-scroll');
                }
                
                // Close mobile menu
                if (navMenu && navMenu.classList.contains('active')) {
                    navMenu.classList.remove('active');
                    hamburger.classList.remove('active');
                    body.classList.remove('no-scroll');
                    
                    const icon = hamburger.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                }
            }
        });
    }
    
    // ===== INITIALIZE EVERYTHING =====
    function init() {
        console.log('Initializing Awalan Design.id website...');
        
        // Initialize components
        initMobileNavigation();
        initHeaderScroll();
        initBackToTop();
        initPortfolioFilter();
        initPricingTabs();
        initTestimonialSlider();
        initContactForm();
        initSmoothScroll();
        initStatsCounter();
        updateCurrentYear();
        initLazyLoad();
        initPreloader();
        initThemeToggle();
        initWhatsAppButton();
        initKeyboardShortcuts();
        
        // Initial animations
        setTimeout(() => {
            animateOnScroll();
        }, 100);
        
        // Add animation keyframes
        addAnimationKeyframes();
        
        console.log('Website initialized successfully!');
    }
    
    // ===== ADD ANIMATION KEYFRAMES =====
    function addAnimationKeyframes() {
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            @keyframes slideInRight {
                from {
                    opacity: 0;
                    transform: translateX(30px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            
            @keyframes slideOutRight {
                from {
                    opacity: 1;
                    transform: translateX(0);
                }
                to {
                    opacity: 0;
                    transform: translateX(30px);
                }
            }
            
            @keyframes pulse {
                0% {
                    box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7);
                }
                70% {
                    box-shadow: 0 0 0 10px rgba(37, 211, 102, 0);
                }
                100% {
                    box-shadow: 0 0 0 0 rgba(37, 211, 102, 0);
                }
            }
            
            @keyframes float {
                0%, 100% {
                    transform: translateY(0);
                }
                50% {
                    transform: translateY(-10px);
                }
            }
            
            .pulse {
                animation: pulse 1s infinite;
            }
            
            .no-scroll {
                overflow: hidden;
            }
            
            /* Dark theme styles */
            .dark-theme {
                background-color: #1a1a1a;
                color: #f0f0f0;
            }
            
            .dark-theme .section-light {
                background-color: #2d2d2d;
            }
            
            .dark-theme .service-card,
            .dark-theme .pricing-card,
            .dark-theme .testimonial-card,
            .dark-theme .contact-form {
                background-color: #2d2d2d;
                color: #f0f0f0;
            }
            
            .dark-theme h1,
            .dark-theme h2,
            .dark-theme h3,
            .dark-theme h4,
            .dark-theme p {
                color: #f0f0f0;
            }
            
            .dark-theme .form-control {
                background-color: #3d3d3d;
                border-color: #4d4d4d;
                color: #f0f0f0;
            }
        `;
        
        document.head.appendChild(style);
    }
    
    // ===== START INITIALIZATION =====
    init();
    
    // Expose some functions globally for debugging
    window.awalanDesign = {
        showNotification,
        refreshAnimations: animateOnScroll
    };
});

// Handle errors gracefully
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.message, e.filename, e.lineno);
});

// Log page visibility changes
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        console.log('Page hidden');
    } else {
        console.log('Page visible');
    }
});
<?php
if (!defined('ABSPATH')) exit;

class Retro_Gallery_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'retro_gallery';
    }

    public function get_title() {
        return __('Retro Gallery', 'retro-gallery');
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    public function get_categories() {
        return ['general'];
    }

    protected function register_controls() {
        
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Gallery Settings', 'retro-gallery'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'gallery_title',
            [
                'label' => __('Gallery Title', 'retro-gallery'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('RETRO GALLERY', 'retro-gallery'),
            ]
        );

        $taxonomies = get_taxonomies(['public' => true], 'objects');
        $taxonomy_options = ['none' => 'No Filter'];
        foreach ($taxonomies as $taxonomy) {
            $taxonomy_options[$taxonomy->name] = $taxonomy->label;
        }

        $this->add_control(
            'taxonomy',
            [
                'label' => __('Filter by Taxonomy', 'retro-gallery'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $taxonomy_options,
                'default' => 'none',
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label' => __('Number of Images', 'retro-gallery'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 12,
                'min' => 1,
                'max' => 100,
            ]
        );

        $this->add_control(
            'image_size',
            [
                'label' => __('Image Size', 'retro-gallery'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'thumbnail' => 'Thumbnail',
                    'medium' => 'Medium',
                    'large' => 'Large',
                    'full' => 'Full',
                ],
                'default' => 'large',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Colors', 'retro-gallery'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'primary_color',
            [
                'label' => __('Primary Color', 'retro-gallery'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#FF6B35',
            ]
        );

        $this->add_control(
            'secondary_color',
            [
                'label' => __('Secondary Color', 'retro-gallery'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#00A8CC',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $args = [
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => 'image',
            'posts_per_page' => $settings['posts_per_page'],
            'orderby' => 'date',
            'order' => 'DESC'
        ];

        if ($settings['taxonomy'] !== 'none') {
            $terms = get_terms([
                'taxonomy' => $settings['taxonomy'],
                'hide_empty' => true,
            ]);
        }

        $query = new WP_Query($args);
        $images = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $image_url = wp_get_attachment_image_url($post_id, $settings['image_size']);
                $alt = get_post_meta($post_id, '_wp_attachment_image_alt', true);
                $description = get_the_excerpt();
                
                $image_terms = [];
                if ($settings['taxonomy'] !== 'none') {
                    $post_terms = wp_get_post_terms($post_id, $settings['taxonomy']);
                    foreach ($post_terms as $term) {
                        $image_terms[] = $term->slug;
                    }
                }

                $images[] = [
                    'id' => $post_id,
                    'src' => $image_url,
                    'alt' => $alt ?: get_the_title(),
                    'description' => $description ?: 'No description available.',
                    'taxonomy' => !empty($image_terms) ? $image_terms[0] : 'all'
                ];
            }
            wp_reset_postdata();
        }
        ?>

        <style>
            .retro-gallery-<?php echo $this->get_id(); ?> {
                --retro-primary: <?php echo esc_attr($settings['primary_color']); ?>;
                --retro-secondary: <?php echo esc_attr($settings['secondary_color']); ?>;
                --retro-yellow: #F7B801;
                --retro-pink: #FF8FA3;
                --retro-dark: #2C1810;
                --retro-beige: #F4E8D0;
                --shadow-retro: 4px 4px 0px rgba(0, 0, 0, 0.3);
                --bg-light: #FFF8E7;
                --text-light: #2C1810;
                --bg-dark: #1a1a1a;
                --text-dark: #F4E8D0;
            }

            @media (prefers-color-scheme: dark) {
                .retro-gallery-<?php echo $this->get_id(); ?> {
                    --bg-primary: var(--bg-dark);
                    --text-primary: var(--text-dark);
                    --card-bg: #2a2a2a;
                }
            }

            @media (prefers-color-scheme: light) {
                .retro-gallery-<?php echo $this->get_id(); ?> {
                    --bg-primary: var(--bg-light);
                    --text-primary: var(--text-light);
                    --card-bg: #fff;
                }
            }

            .retro-gallery-<?php echo $this->get_id(); ?> {
                font-family: 'Courier New', monospace;
                background-color: var(--bg-primary);
                color: var(--text-primary);
                padding: 1rem;
            }

            .retro-gallery-header-<?php echo $this->get_id(); ?> {
                text-align: center;
                margin-bottom: 2rem;
                padding: 1.5rem;
                background: linear-gradient(135deg, var(--retro-primary), var(--retro-pink));
                border: 4px solid var(--retro-dark);
                box-shadow: var(--shadow-retro);
            }

            .retro-gallery-title-<?php echo $this->get_id(); ?> {
                font-size: 2rem;
                font-weight: bold;
                color: var(--retro-beige);
                text-shadow: 3px 3px 0px rgba(0, 0, 0, 0.5);
                letter-spacing: 2px;
                margin: 0;
            }

            .retro-taxonomy-filter-<?php echo $this->get_id(); ?> {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                justify-content: center;
                margin-bottom: 2rem;
            }

            .retro-filter-btn-<?php echo $this->get_id(); ?> {
                padding: 0.75rem 1.5rem;
                background-color: var(--retro-secondary);
                color: var(--retro-beige);
                border: 3px solid var(--retro-dark);
                box-shadow: var(--shadow-retro);
                cursor: pointer;
                font-family: 'Courier New', monospace;
                font-weight: bold;
                font-size: 0.9rem;
                transition: transform 0.2s ease;
            }

            .retro-filter-btn-<?php echo $this->get_id(); ?>:hover,
            .retro-filter-btn-<?php echo $this->get_id(); ?>.active {
                background-color: var(--retro-yellow);
                transform: translate(2px, 2px);
                box-shadow: 2px 2px 0px rgba(0, 0, 0, 0.3);
            }

            .retro-gallery-grid-<?php echo $this->get_id(); ?> {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 1.5rem;
                margin-bottom: 2rem;
            }

            .retro-gallery-item-<?php echo $this->get_id(); ?> {
                position: relative;
                overflow: hidden;
                border: 4px solid var(--retro-dark);
                box-shadow: var(--shadow-retro);
                cursor: pointer;
                transition: transform 0.3s ease;
                background-color: var(--card-bg);
            }

            .retro-gallery-item-<?php echo $this->get_id(); ?>:hover {
                transform: scale(1.05) rotate(-1deg);
            }

            .retro-gallery-item-<?php echo $this->get_id(); ?> img {
                width: 100%;
                height: 250px;
                object-fit: cover;
                display: block;
            }

            .retro-gallery-item-overlay-<?php echo $this->get_id(); ?> {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
                padding: 1rem;
                transform: translateY(100%);
                transition: transform 0.3s ease;
            }

            .retro-gallery-item-<?php echo $this->get_id(); ?>:hover .retro-gallery-item-overlay-<?php echo $this->get_id(); ?> {
                transform: translateY(0);
            }

            .retro-overlay-title-<?php echo $this->get_id(); ?> {
                color: var(--retro-yellow);
                font-weight: bold;
                font-size: 0.9rem;
            }

            .retro-lightbox-<?php echo $this->get_id(); ?> {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.95);
                z-index: 9999;
                overflow-y: auto;
            }

            .retro-lightbox-<?php echo $this->get_id(); ?>.active {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1rem;
            }

            .retro-lightbox-content-<?php echo $this->get_id(); ?> {
                display: flex;
                flex-direction: column;
                max-width: 1400px;
                width: 100%;
                gap: 1.5rem;
            }

            .retro-lightbox-main-<?php echo $this->get_id(); ?> {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }

            .retro-lightbox-image-container-<?php echo $this->get_id(); ?> {
                position: relative;
                background-color: var(--card-bg);
                border: 6px solid var(--retro-primary);
                box-shadow: 8px 8px 0px rgba(255, 107, 53, 0.4);
            }

            .retro-lightbox-image-<?php echo $this->get_id(); ?> {
                width: 100%;
                max-height: 70vh;
                object-fit: contain;
                display: block;
            }

            .retro-lightbox-meta-<?php echo $this->get_id(); ?> {
                background: linear-gradient(135deg, var(--retro-secondary), var(--retro-pink));
                padding: 1.5rem;
                border: 4px solid var(--retro-dark);
                box-shadow: var(--shadow-retro);
            }

            .retro-lightbox-description-<?php echo $this->get_id(); ?> {
                color: var(--retro-beige);
                font-size: 1rem;
                line-height: 1.6;
                margin-bottom: 1rem;
            }

            .retro-lightbox-controls-<?php echo $this->get_id(); ?> {
                display: flex;
                flex-wrap: wrap;
                gap: 0.75rem;
                justify-content: center;
                margin-top: 1rem;
            }

            .retro-lightbox-btn-<?php echo $this->get_id(); ?> {
                padding: 0.75rem 1.25rem;
                background-color: var(--retro-yellow);
                color: var(--retro-dark);
                border: 3px solid var(--retro-dark);
                box-shadow: var(--shadow-retro);
                cursor: pointer;
                font-family: 'Courier New', monospace;
                font-weight: bold;
                font-size: 0.85rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                transition: transform 0.2s ease;
            }

            .retro-lightbox-btn-<?php echo $this->get_id(); ?>:hover {
                transform: translate(2px, 2px);
                box-shadow: 2px 2px 0px rgba(0, 0, 0, 0.3);
            }

            .retro-nav-btn-<?php echo $this->get_id(); ?> {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                background-color: var(--retro-primary);
                color: var(--retro-beige);
                border: 3px solid var(--retro-dark);
                padding: 1rem;
                cursor: pointer;
                font-size: 1.5rem;
                font-weight: bold;
                z-index: 10;
                box-shadow: var(--shadow-retro);
            }

            .retro-nav-btn-<?php echo $this->get_id(); ?>:hover {
                background-color: var(--retro-yellow);
            }

            .retro-nav-prev-<?php echo $this->get_id(); ?> {
                left: 1rem;
            }

            .retro-nav-next-<?php echo $this->get_id(); ?> {
                right: 1rem;
            }

            .retro-close-btn-<?php echo $this->get_id(); ?> {
                position: absolute;
                top: 1rem;
                right: 1rem;
                background-color: var(--retro-pink);
                color: var(--retro-dark);
                border: 3px solid var(--retro-dark);
                padding: 0.75rem 1rem;
                cursor: pointer;
                font-size: 1.25rem;
                font-weight: bold;
                box-shadow: var(--shadow-retro);
                z-index: 11;
            }

            .retro-close-btn-<?php echo $this->get_id(); ?>:hover {
                background-color: var(--retro-yellow);
            }

            .retro-share-label-<?php echo $this->get_id(); ?> {
                font-weight: bold;
                color: var(--retro-beige);
                margin-bottom: 0.5rem;
            }

            @media (min-width: 768px) {
                .retro-gallery-grid-<?php echo $this->get_id(); ?> {
                    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                }

                .retro-gallery-item-<?php echo $this->get_id(); ?> img {
                    height: 300px;
                }

                .retro-gallery-title-<?php echo $this->get_id(); ?> {
                    font-size: 3rem;
                }

                .retro-lightbox-main-<?php echo $this->get_id(); ?> {
                    flex-direction: row;
                }

                .retro-lightbox-image-container-<?php echo $this->get_id(); ?> {
                    flex: 2;
                }

                .retro-lightbox-meta-<?php echo $this->get_id(); ?> {
                    flex: 1;
                    max-width: 400px;
                }
            }
        </style>

        <div class="retro-gallery-<?php echo $this->get_id(); ?>">
            <div class="retro-gallery-header-<?php echo $this->get_id(); ?>">
                <h2 class="retro-gallery-title-<?php echo $this->get_id(); ?>"><?php echo esc_html($settings['gallery_title']); ?></h2>
            </div>

            <?php if ($settings['taxonomy'] !== 'none' && !empty($terms)) : ?>
            <div class="retro-taxonomy-filter-<?php echo $this->get_id(); ?>">
                <button class="retro-filter-btn-<?php echo $this->get_id(); ?> active" data-taxonomy="all">ALL</button>
                <?php foreach ($terms as $term) : ?>
                    <button class="retro-filter-btn-<?php echo $this->get_id(); ?>" data-taxonomy="<?php echo esc_attr($term->slug); ?>">
                        <?php echo esc_html(strtoupper($term->name)); ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="retro-gallery-grid-<?php echo $this->get_id(); ?>" id="galleryGrid<?php echo $this->get_id(); ?>"></div>
        </div>

        <div class="retro-lightbox-<?php echo $this->get_id(); ?>" id="lightbox<?php echo $this->get_id(); ?>">
            <button class="retro-close-btn-<?php echo $this->get_id(); ?>" id="closeBtn<?php echo $this->get_id(); ?>">✕</button>
            <div class="retro-lightbox-content-<?php echo $this->get_id(); ?>">
                <div class="retro-lightbox-main-<?php echo $this->get_id(); ?>">
                    <div class="retro-lightbox-image-container-<?php echo $this->get_id(); ?>">
                        <button class="retro-nav-btn-<?php echo $this->get_id(); ?> retro-nav-prev-<?php echo $this->get_id(); ?>" id="prevBtn<?php echo $this->get_id(); ?>">‹</button>
                        <img class="retro-lightbox-image-<?php echo $this->get_id(); ?>" id="lightboxImage<?php echo $this->get_id(); ?>" src="" alt="">
                        <button class="retro-nav-btn-<?php echo $this->get_id(); ?> retro-nav-next-<?php echo $this->get_id(); ?>" id="nextBtn<?php echo $this->get_id(); ?>">›</button>
                    </div>
                    <div class="retro-lightbox-meta-<?php echo $this->get_id(); ?>">
                        <p class="retro-lightbox-description-<?php echo $this->get_id(); ?>" id="lightboxDescription<?php echo $this->get_id(); ?>"></p>
                        <div class="retro-share-label-<?php echo $this->get_id(); ?>">SHARE THIS:</div>
                        <div class="retro-lightbox-controls-<?php echo $this->get_id(); ?>">
                            <button class="retro-lightbox-btn-<?php echo $this->get_id(); ?>" data-share="facebook">
                                <span>📘</span> FACEBOOK
                            </button>
                            <button class="retro-lightbox-btn-<?php echo $this->get_id(); ?>" data-share="twitter">
                                <span>🐦</span> TWITTER
                            </button>
                            <button class="retro-lightbox-btn-<?php echo $this->get_id(); ?>" data-share="pinterest">
                                <span>📌</span> PINTEREST
                            </button>
                            <button class="retro-lightbox-btn-<?php echo $this->get_id(); ?>" data-share="whatsapp">
                                <span>💬</span> WHATSAPP
                            </button>
                            <button class="retro-lightbox-btn-<?php echo $this->get_id(); ?>" data-share="copy">
                                <span>🔗</span> COPY LINK
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        (function() {
            const widgetId = '<?php echo $this->get_id(); ?>';
            const galleryData = <?php echo json_encode($images); ?>;
            let currentImageIndex = 0;
            let filteredImages = [...galleryData];

            function renderGallery(images) {
                const grid = document.getElementById('galleryGrid' + widgetId);
                grid.innerHTML = images.map((img, index) => `
                    <div class="retro-gallery-item-${widgetId}" data-index="${index}">
                        <img src="${img.src}" alt="${img.alt}" loading="lazy">
                        <div class="retro-gallery-item-overlay-${widgetId}">
                            <div class="retro-overlay-title-${widgetId}">${img.alt}</div>
                        </div>
                    </div>
                `).join('');

                document.querySelectorAll('.retro-gallery-item-' + widgetId).forEach(item => {
                    item.addEventListener('click', () => {
                        currentImageIndex = parseInt(item.dataset.index);
                        openLightbox();
                    });
                });
            }

            function openLightbox() {
                const lightbox = document.getElementById('lightbox' + widgetId);
                lightbox.classList.add('active');
                document.body.style.overflow = 'hidden';
                updateLightboxContent();
            }

            function closeLightbox() {
                const lightbox = document.getElementById('lightbox' + widgetId);
                lightbox.classList.remove('active');
                document.body.style.overflow = '';
            }

            function updateLightboxContent() {
                const img = filteredImages[currentImageIndex];
                document.getElementById('lightboxImage' + widgetId).src = img.src;
                document.getElementById('lightboxImage' + widgetId).alt = img.alt;
                document.getElementById('lightboxDescription' + widgetId).textContent = img.description;
            }

            function navigateImage(direction) {
                currentImageIndex += direction;
                if (currentImageIndex < 0) currentImageIndex = filteredImages.length - 1;
                if (currentImageIndex >= filteredImages.length) currentImageIndex = 0;
                updateLightboxContent();
            }

            function shareToSocial(platform) {
                const img = filteredImages[currentImageIndex];
                const url = encodeURIComponent(window.location.href);
                const text = encodeURIComponent(img.alt + ' - ' + img.description);
                const imageUrl = encodeURIComponent(img.src);

                let shareUrl = '';
                switch(platform) {
                    case 'facebook':
                        shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                        break;
                    case 'twitter':
                        shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${text}`;
                        break;
                    case 'pinterest':
                        shareUrl = `https://pinterest.com/pin/create/button/?url=${url}&media=${imageUrl}&description=${text}`;
                        break;
                    case 'whatsapp':
                        shareUrl = `https://wa.me/?text=${text}%20${url}`;
                        break;
                    case 'copy':
                        navigator.clipboard.writeText(window.location.href).then(() => {
                            alert('Link copied to clipboard!');
                        });
                        return;
                }

                if (shareUrl) {
                    window.open(shareUrl, '_blank', 'width=600,height=400');
                }
            }

            document.getElementById('closeBtn' + widgetId).addEventListener('click', closeLightbox);
            document.getElementById('prevBtn' + widgetId).addEventListener('click', () => navigateImage(-1));
            document.getElementById('nextBtn' + widgetId).addEventListener('click', () => navigateImage(1));

            document.querySelectorAll('.retro-lightbox-btn-' + widgetId).forEach(btn => {
                btn.addEventListener('click', () => {
                    shareToSocial(btn.dataset.share);
                });
            });

            document.getElementById('lightbox' + widgetId).addEventListener('click', (e) => {
                if (e.target.id === 'lightbox' + widgetId) {
                    closeLightbox();
                }
            });

            document.addEventListener('keydown', (e) => {
                if (!document.getElementById('lightbox' + widgetId).classList.contains('active')) return;
                if (e.key === 'Escape') closeLightbox();
                if (e.key === 'ArrowLeft') navigateImage(-1);
                if (e.key === 'ArrowRight') navigateImage(1);
            });

            document.querySelectorAll('.retro-filter-btn-' + widgetId).forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.retro-filter-btn-' + widgetId).forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');

                    const taxonomy = btn.dataset.taxonomy;
                    filteredImages = taxonomy === 'all' 
                        ? [...galleryData] 
                        : galleryData.filter(img => img.taxonomy === taxonomy);
                    
                    renderGallery(filteredImages);
                });
            });

            renderGallery(filteredImages);
        })();
        </script>

        <?php
    }
}
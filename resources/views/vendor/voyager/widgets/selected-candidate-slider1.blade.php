{{-- resources/views/vendor/voyager/widgets/sponsor-slider.blade.php --}}

<style>
    .sponsor-slider-widget {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 20px;
    }

    .sponsor-slider-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 15px 20px;
        color: white;
    }

    .sponsor-slider-header h3 {
        font-size: 18px;
        margin: 0;
        font-weight: 600;
    }

    .sponsor-slider-header p {
        font-size: 12px;
        margin: 5px 0 0 0;
        opacity: 0.9;
    }

    .sponsor-slider-wrapper {
        position: relative;
        height: 600px;
        background: #fafafa;
        overflow: hidden;
        padding: 20px 60px;
    }

    .sponsor-slider-track {
        position: relative;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .sponsor-slide {
        position: absolute;
        width: 100%;
        height: 100%;
        /*display: grid;*/
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        opacity: 0;
        transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
        transform: scale(0.95);
        padding: 0 10px;
    }

    .sponsor-slide.active {
        opacity: 1;
        transform: scale(1);
    }

    .sponsor-card {
        background: white;
        border-radius: 12px;
        padding: 25px 20px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border: 1px solid #f0f0f0;
    }

    .sponsor-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }

    .sponsor-card a {
        text-decoration: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
    }

    .sponsor-image-wrapper {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        overflow: hidden;
        margin-bottom: 15px;
        border: 3px solid #667eea;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
    }

    .sponsor-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .sponsor-name {
        color: #333;
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 5px;
        line-height: 1.3;
    }

    .sponsor-designation {
        color: #667eea;
        font-size: 13px;
        font-weight: 500;
        margin-bottom: 8px;
    }

    .sponsor-company {
        color: #666;
        font-size: 12px;
        font-style: italic;
    }

    .sponsor-nav-button {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255, 255, 255, 0.95);
        border: none;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
        z-index: 10;
    }

    .sponsor-nav-button:hover {
        background: white;
        transform: translateY(-50%) scale(1.1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .sponsor-nav-button.prev {
        left: 10px;
    }

    .sponsor-nav-button.next {
        right: 10px;
    }

    .sponsor-nav-button i {
        font-size: 20px;
        color: #333;
    }

    .sponsor-dots-container {
        position: absolute;
        bottom: 15px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 8px;
        z-index: 10;
    }

    .sponsor-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #ddd;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
    }

    .sponsor-dot.active {
        background: #667eea;
        width: 24px;
        border-radius: 5px;
    }

    .sponsor-slider-footer {
        padding: 12px 20px;
        background: #f8f8f8;
        text-align: center;
        color: #666;
        font-size: 13px;
        border-top: 1px solid #eee;
    }

    /* Responsive Design */
    @media (max-width: 992px) {
        .sponsor-slide {
            grid-template-columns: repeat(2, 1fr);
        }

        .sponsor-card:nth-child(3) {
            display: none;
        }
    }

    @media (max-width: 576px) {
        .sponsor-slide {
            grid-template-columns: 1fr;
        }

        .sponsor-card:nth-child(2),
        .sponsor-card:nth-child(3) {
            display: none;
        }

        .sponsor-slider-wrapper {
            padding: 20px 50px;
            height: 280px;
        }

        .sponsor-nav-button {
            width: 35px;
            height: 35px;
        }

        .sponsor-nav-button i {
            font-size: 16px;
        }
    }
</style>

<div class="sponsor-slider-widget">
    {{--<div class="sponsor-slider-header">
        <h3><i class="voyager-star"></i> Selected Candidates</h3>
        --}}{{--        <p>LIST FROM HOTEL</p>--}}{{--
    </div>--}}

    <div class="sponsor-slider-wrapper" id="sponsorSliderWrapper">
        <div class="sponsor-slider-track">
            @php
                $chunkedSponsors = array_chunk($sponsors, 1);
            @endphp

            @foreach($chunkedSponsors as $index => $sponsorGroup)
                <div class="sponsor-slide {{ $index === 0 ? 'active' : '' }}">
                    @foreach($sponsorGroup as $sponsor)
                        <div class="sponsor-card">
                            <div>
                                <span style="background: linear-gradient(90deg, #6c757d 0%, #495057 100%); color: white; padding: 15px 30px; border-radius: 50px; display: inline-block; font-size: clamp(20px, 4vw, 32px); font-weight: bold; letter-spacing: 3px; margin-bottom: 2px; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
                                    CONGRATULATIONS
                                    <span style="display: inline-block; font-size: clamp(18px, 3.5vw, 28px); margin-left: 15px;">🎉🎉</span>
                                </span>
                            </div>

                            <div style="width:100%; display: flex; align-items: center; justify-content: space-between; margin: 10px 0; gap: 20px; flex-wrap: wrap;">
                                <div style="background: linear-gradient(90deg, #6c757d 0%, #495057 100%); color: white; padding: 12px 25px; border-radius: 50px; font-size: clamp(11px, 2vw, 14px); letter-spacing: 2px; text-align: center; flex: 1; box-shadow: 0 5px 15px rgba(0,0,0,0.2); min-width: 180px;">
                                    FOR SELECTED FROM
                                </div>

                                <div>
                                    <img src="{{ $sponsor['image'] ?? $sponsor['logo'] }}" alt="{{ $sponsor['name'] }}" style="width: clamp(120px, 25vw, 180px); height: clamp(120px, 25vw, 180px); border-radius: 50%; object-fit: cover; border: 5px solid #667eea; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                                </div>

                                <div style="background: linear-gradient(90deg, #6c757d 0%, #495057 100%); color: white; padding: 12px 25px; border-radius: 50px; font-size: clamp(11px, 2vw, 14px); letter-spacing: 2px; text-align: center; flex: 1; box-shadow: 0 5px 15px rgba(0,0,0,0.2); min-width: 180px;">
                                    CYPRUS REPUTED COMPANY
                                </div>
                            </div>

                            <div style="width:100%;  display: flex; align-items: center; justify-content: space-between; gap: 15px; margin: 0; flex-wrap: wrap;">
                                <div style="background: linear-gradient(90deg, #495057 0%, #343a40 100%); color: white; padding: 8px 15px; border-radius: 25px; font-size: clamp(8px, 1.5vw, 10px); letter-spacing: 1px; text-align: center; flex: 1; width: 35%;">
                                    PLEASE SEND US YOUR POLICE CLEARANCE CERTIFICATE BY YOUR AGENT INSTRUCTIONS.
                                    PLEASE COMPLETE YOUR 3 DAYS BMET TRAINING WITH FINGER PRINT.
                                </div>

                                <div style="font-size: clamp(24px, 5vw, 36px); font-weight: bold; color: #333; letter-spacing: 2px; flex: 0 0 auto; width: 30%;">
                                    {{ $sponsor['name'] }}
                                </div>

                                <div style="background: linear-gradient(90deg, #495057 0%, #343a40 100%); color: white; padding: 8px 15px; border-radius: 25px; font-size: clamp(8px, 1.5vw, 10px); letter-spacing: 1px; text-align: center; flex: 1; width: 35%;">
                                    PLEASE COMPLETE YOUR MEDICAL REPORT FROM YOUR OWN CITY AS PER YOUR AGENT INSTRUCTIONS.
                                    PLEASE SEND YOUR ORIGINAL PASSPORT BY YOUR AGENT INSTRUCTIONS.
                                </div>
                            </div>

                            <div style="margin-top: 0px;">
                                <span style="text-transform: uppercase; background: linear-gradient(90deg, #6c757d 0%, #495057 100%); color: white; padding: 12px 30px; border-radius: 50px; display: inline-block; margin: 10px; font-size: clamp(12px, 2.5vw, 16px); letter-spacing: 1px; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
                                    POSITION : {{ $sponsor['position'] ?? '' }}
                                </span>
                            </div>

                            <div style="margin-top: 0px;">
                                <span style="background: linear-gradient(90deg, #6c757d 0%, #495057 100%); color: white; padding: 12px 30px; border-radius: 50px; display: inline-block; margin: 10px; font-size: clamp(12px, 2.5vw, 16px); letter-spacing: 1px; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
                                    PASSPORT NO : {{ $sponsor['passport_no'] ?? '' }}
                                </span>
                            </div>


                            {{--<a href="{{ $sponsor['website'] ?? '#' }}" target="_blank" rel="noopener">
                                <h1>CONGRATULATIONS 🎉🎉</h1>

                                <div class="row">
                                    <h3>FOR SELECTED FROM</h3>
                                <div class="col-md-4 sponsor-image-wrapper">
                                    <img src="{{ $sponsor['image'] ?? $sponsor['logo'] }}" alt="{{ $sponsor['name'] }}">
                                </div>
                                    <h3>CYPRUS REPUTED COMPANY</h3>
                                </div>

                                <div class="sponsor-name">{{ $sponsor['name'] }}</div>
                                <div class="sponsor-designation" style="text-transform: capitalize;">
                                    <h3>POSITION: {{ $sponsor['position'] ?? '' }}</h3>
                                    <h3>PASSPORT NO: {{ $sponsor['passport_no'] ?? '' }}</h3>
                                </div>
                            </a>--}}
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        <button class="sponsor-nav-button prev" onclick="sponsorPrevSlide()" aria-label="Previous">
            <i class="voyager-angle-left"></i>
        </button>
        <button class="sponsor-nav-button next" onclick="sponsorNextSlide()" aria-label="Next">
            <i class="voyager-angle-right"></i>
        </button>

        <div class="sponsor-dots-container" id="sponsorDotsContainer"></div>
    </div>

    {{--<div class="sponsor-slider-footer">
        <span id="sponsorSlideCounter">Showing 1-{{ min(1, count($sponsors)) }} of {{ count($sponsors) }} candidates</span>
    </div>--}}
</div>

<script>
    (function() {
        let sponsorCurrentSlide = 0;
        let sponsorAutoPlayInterval;
        const sponsorSlides = document.querySelectorAll('.sponsor-slide');
        const sponsorTotalSlides = sponsorSlides.length;
        const sponsorSliderWrapper = document.getElementById('sponsorSliderWrapper');
        const sponsorDotsContainer = document.getElementById('sponsorDotsContainer');
        const sponsorSlideCounter = document.getElementById('sponsorSlideCounter');
        const totalSponsors = {{ count($sponsors) }};
        const sponsorsPerSlide = 1;

        // Create dots
        function createSponsorDots() {
            for (let i = 0; i < sponsorTotalSlides; i++) {
                const dot = document.createElement('button');
                dot.classList.add('sponsor-dot');
                if (i === 0) dot.classList.add('active');
                dot.setAttribute('aria-label', `Go to slide ${i + 1}`);
                dot.onclick = () => goToSponsorSlide(i);
                sponsorDotsContainer.appendChild(dot);
            }
        }

        // Update counter
        function updateSponsorCounter() {
            const startNum = (sponsorCurrentSlide * sponsorsPerSlide) + 1;
            const endNum = Math.min((sponsorCurrentSlide + 1) * sponsorsPerSlide, totalSponsors);
            sponsorSlideCounter.textContent = `Showing ${startNum}-${endNum} of ${totalSponsors} sponsors`;
        }

        // Show slide
        function showSponsorSlide(index) {
            sponsorSlides.forEach((slide, i) => {
                slide.classList.toggle('active', i === index);
            });

            const dots = document.querySelectorAll('.sponsor-dot');
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === index);
            });

            updateSponsorCounter();
        }

        // Next slide
        window.sponsorNextSlide = function() {
            sponsorCurrentSlide = (sponsorCurrentSlide + 1) % sponsorTotalSlides;
            showSponsorSlide(sponsorCurrentSlide);
            resetSponsorAutoPlay();
        }

        // Previous slide
        window.sponsorPrevSlide = function() {
            sponsorCurrentSlide = (sponsorCurrentSlide - 1 + sponsorTotalSlides) % sponsorTotalSlides;
            showSponsorSlide(sponsorCurrentSlide);
            resetSponsorAutoPlay();
        }

        // Go to specific slide
        function goToSponsorSlide(index) {
            sponsorCurrentSlide = index;
            showSponsorSlide(sponsorCurrentSlide);
            resetSponsorAutoPlay();
        }

        // Auto play
        function startSponsorAutoPlay() {
            sponsorAutoPlayInterval = setInterval(sponsorNextSlide, 4000);
        }

        function stopSponsorAutoPlay() {
            clearInterval(sponsorAutoPlayInterval);
        }

        function resetSponsorAutoPlay() {
            stopSponsorAutoPlay();
            startSponsorAutoPlay();
        }

        // Pause on hover
        if (sponsorSliderWrapper) {
            sponsorSliderWrapper.addEventListener('mouseenter', stopSponsorAutoPlay);
            sponsorSliderWrapper.addEventListener('mouseleave', startSponsorAutoPlay);
        }

        // Initialize
        if (sponsorTotalSlides > 0) {
            createSponsorDots();
            startSponsorAutoPlay();
        }
    })();
</script>

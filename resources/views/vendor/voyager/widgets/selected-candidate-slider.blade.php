@php
    $candidate = \DB::select(" SELECT sc.*, c.name, c.passport_no, c.half_photo_file_path
             FROM selected_candidates AS sc
             INNER JOIN candidates AS c  ON c.id = sc.candidate_id
            ORDER BY sc.id DESC");

    $sponsors = [];
    if($candidate){
        foreach ($candidate as $s){
            $sponsors[] = [
                'name' => $s->name,
                'position' => strtoupper($s->position),
                'passport_no' =>$s->passport_no,
                'image' =>!empty($s->half_photo_file_path)?Voyager::image($s->half_photo_file_path):'',
                'website' => '#'
            ];
        }
    }else{
        $sponsors = [];
    }
@endphp

@if(count($sponsors) > 0)
<style>
    .slider-container {
        max-width: 1400px;
        margin: 0 auto 24px auto;
        position: relative;
        padding: 0 45px;
    }

    .card-wrapper {
        display: none;
    }

    .card-wrapper.active {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        animation: slideInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .congrats-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.06), 0 2px 4px -1px rgba(15, 23, 42, 0.02);
        border: 1px solid rgba(226, 232, 240, 0.8);
        text-align: center;
        display: flex;
        flex-direction: column;
        height: 100%;
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s ease, border-color 0.25s ease;
        position: relative;
        overflow: hidden;
    }

    .congrats-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, {{ config('voyager.primary_color', '#22A7F0') }} 0%, #3b82f6 100%);
    }

    .congrats-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 25px -5px rgba(15, 23, 42, 0.1), 0 8px 10px -6px rgba(15, 23, 42, 0.05);
        border-color: rgba(99, 102, 241, 0.2);
    }

    .header-badge {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        color: #78350f;
        padding: 6px 16px;
        border-radius: 30px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        display: inline-block;
        margin: 0 auto 16px auto;
        box-shadow: 0 4px 10px rgba(245, 158, 11, 0.15);
    }

    .info-section {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 16px;
    }

    .info-box {
        background: rgba(99, 102, 241, 0.06);
        color: #4f46e5;
        border: 1px solid rgba(99, 102, 241, 0.1);
        padding: 6px 14px;
        border-radius: 30px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        display: inline-block;
        margin: 0 auto;
    }

    .profile-section {
        margin: 16px 0;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .profile-image {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 3px solid #ffffff;
        box-shadow: 0 0 0 2px {{ config('voyager.primary_color', '#22A7F0') }};
        object-fit: cover;
        margin: 0 auto 12px auto;
        display: block;
        transition: transform 0.3s ease;
    }

    .congrats-card:hover .profile-image {
        transform: scale(1.06);
    }

    .name {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        margin: 10px 0 12px 0;
        text-transform: uppercase;
        letter-spacing: 1px;
        word-break: break-word;
    }

    .detail-box {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        padding: 8px 16px;
        border-radius: 30px;
        margin: 6px auto;
        font-size: 12px;
        font-weight: 600;
        max-width: 100%;
        display: inline-block;
    }

    .instruction-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
        padding: 14px;
        border-radius: 12px;
        font-size: 11px;
        line-height: 1.5;
        margin-top: 16px;
        text-align: left;
    }

    .instruction-box span {
        color: #ef4444;
        font-weight: 700;
    }

    .nav-button {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: #ffffff;
        border: 1px solid #e2e8f0;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        font-size: 20px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
        transition: all 0.2s ease;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #475569;
        outline: none;
    }

    .nav-button:hover {
        background: {{ config('voyager.primary_color', '#22A7F0') }};
        color: #ffffff;
        border-color: {{ config('voyager.primary_color', '#22A7F0') }};
        box-shadow: 0 4px 20px rgba(99, 102, 241, 0.25);
    }

    .nav-button.prev {
        left: -10px;
    }

    .nav-button.next {
        right: -10px;
    }

    .dots-container {
        text-align: center;
        margin-top: 24px;
        display: flex;
        justify-content: center;
        gap: 6px;
    }

    .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #cbd5e1;
        display: inline-block;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .dot.active {
        background: {{ config('voyager.primary_color', '#22A7F0') }};
        width: 20px;
        border-radius: 4px;
    }

    /* Tablet */
    @media (max-width: 992px) {
        .slider-container {
            padding: 0 30px;
        }

        .card-wrapper.active {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .congrats-card {
            padding: 20px;
        }

        .name {
            font-size: 16px;
        }
    }

    /* Mobile */
    @media (max-width: 600px) {
        .slider-container {
            padding: 0 20px;
        }

        .card-wrapper.active {
            grid-template-columns: 1fr;
        }

        .nav-button.prev {
            left: -5px;
        }

        .nav-button.next {
            right: -5px;
        }
    }
</style>

<div class="slider-container">
    <button class="nav-button prev" onclick="changeSlide(-1)">‹</button>
    <button class="nav-button next" onclick="changeSlide(1)">›</button>

    <div id="slider">
        @php
            $chunkedSponsors = array_chunk($sponsors, 3);
        @endphp

        @foreach($chunkedSponsors as $index => $sponsorGroup)
            <div class="card-wrapper {{ $index === 0 ? 'active' : '' }}">
                @foreach($sponsorGroup as $i=>$sponsor)
                    <div class="congrats-card">
                        <div class="header-badge">CONGRATULATIONS 🎉</div>
                        <div class="info-section">
                            <div class="info-box">SELECTED FOR COMPANY</div>
                        </div>
                        <div class="profile-section">
                            <img src="{{ $sponsor['image'] }}" alt="{{ $sponsor['name'] }}" class="profile-image">
                            <div class="name">{{ $sponsor['name'] }}</div>
                            <div>
                                <span class="detail-box">POSITION: {{ $sponsor['position'] }}</span>
                            </div>
                            <div>
                                <span class="detail-box">PASSPORT: {{ $sponsor['passport_no'] }}</span>
                            </div>
                        </div>
                        <div class="instruction-box">
                            PLEASE COMPLETE YOUR MEDICAL REPORT FROM YOUR OWN CITY AS PER YOUR AGENT INSTRUCTIONS. PLEASE SEND YOUR <span>ORIGINAL PASSPORT</span> BY YOUR AGENT INSTRUCTIONS.
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    <div class="dots-container" id="dotsContainer"></div>
</div>

<script>
    (function() {
        let currentSlide = 0;
        const slides = document.querySelectorAll('.card-wrapper');
        const totalSlides = slides.length;

        if (totalSlides <= 0) return;

        // Initialize dots
        function initDots() {
            const dotsContainer = document.getElementById('dotsContainer');
            if (!dotsContainer) return;
            dotsContainer.innerHTML = '';
            for (let i = 0; i < totalSlides; i++) {
                const dot = document.createElement('span');
                dot.className = 'dot' + (i === 0 ? ' active' : '');
                dot.onclick = () => goToSlide(i);
                dotsContainer.appendChild(dot);
            }
        }

        window.showSlide = function(n) {
            if (n >= totalSlides) currentSlide = 0;
            else if (n < 0) currentSlide = totalSlides - 1;
            else currentSlide = n;

            slides.forEach(slide => slide.classList.remove('active'));
            if(slides[currentSlide]) slides[currentSlide].classList.add('active');

            const dots = document.querySelectorAll('.dot');
            dots.forEach(dot => dot.classList.remove('active'));
            if(dots[currentSlide]) dots[currentSlide].classList.add('active');
        }

        window.changeSlide = function(direction) {
            showSlide(currentSlide + direction);
        }

        window.goToSlide = function(n) {
            showSlide(n);
        }

        // Auto-play slider
        let slideInterval = setInterval(() => {
            changeSlide(1);
        }, 8000);

        // Reset timer on manual navigation
        function resetTimer() {
            clearInterval(slideInterval);
            slideInterval = setInterval(() => {
                changeSlide(1);
            }, 8000);
        }

        const prevBtn = document.querySelector('.nav-button.prev');
        const nextBtn = document.querySelector('.nav-button.next');
        if (prevBtn) prevBtn.addEventListener('click', resetTimer);
        if (nextBtn) nextBtn.addEventListener('click', resetTimer);

        // Initialize
        initDots();
    })();
</script>
@else
<div style="text-align: center; color: #64748b; padding: 48px; background: #ffffff; border-radius: 16px; border: 1px solid rgba(226, 232, 240, 0.8); margin: 0 auto 24px auto; max-width: 1400px; box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.06);">
    <i class="voyager-trophy" style="font-size: 48px; color: #cbd5e1; margin-bottom: 16px; display: block;"></i>
    <span style="font-weight: 700; font-size: 16px; color: #334155; display: block; margin-bottom: 6px;">No Selections Recorded</span>
    <span style="font-size: 13px; color: #64748b;">There are currently no candidates selected for company placement.</span>
</div>
@endif

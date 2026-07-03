<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="{{ $school->about ?: 'Official website and result portal for '.$school->name }}">
    <title>{{ $school->name }}</title>
    <style>
        :root{--ink:#102a43;--muted:#627d98;--brand:#087f5b;--brand-dark:#064e3b;--soft:#e8f5f0;font-family:Inter,ui-sans-serif,system-ui,sans-serif}
        *{box-sizing:border-box}body{margin:0;color:var(--ink);background:#f7fafc}.nav{display:flex;justify-content:space-between;align-items:center;max-width:1180px;margin:auto;padding:20px}.identity{display:flex;gap:12px;align-items:center;font-weight:900}.logo{width:56px;height:56px;border-radius:14px;object-fit:contain;background:white;border:1px solid #dce7ef;padding:5px}.nav a,.cta{background:var(--brand);color:white;padding:12px 18px;border-radius:11px;text-decoration:none;font-weight:800}.hero-shell{max-width:1180px;margin:22px auto 0;padding:0 20px}.hero{min-height:560px;border-radius:32px;overflow:hidden;position:relative;background:linear-gradient(135deg,#073b3a,#087f5b 62%,#21a179);color:#fff}.hero-static{padding:64px;display:flex;flex-direction:column;justify-content:center}.hero-static:after{content:"";position:absolute;width:420px;height:420px;border-radius:50%;border:86px solid #ffffff17;right:-80px;bottom:-160px}.hero h1{font-size:clamp(42px,7vw,76px);max-width:840px;line-height:1.01;margin:12px 0 20px}.hero p{font-size:20px;line-height:1.7;max-width:720px;color:#dbfff3}.tag{display:inline-block;width:max-content;padding:8px 12px;border:1px solid #ffffff55;border-radius:999px;font-weight:700}.hero .cta{display:inline-block;width:max-content;margin-top:18px;background:white;color:var(--brand);position:relative;z-index:2}.slider{height:560px;position:relative}.slide{position:absolute;inset:0;opacity:0;visibility:hidden;transition:opacity .65s ease,visibility .65s ease;background:#073b3a}.slide.active{opacity:1;visibility:visible}.slide img{width:100%;height:100%;object-fit:cover}.slide:after{content:"";position:absolute;inset:0;background:linear-gradient(90deg,rgba(3,40,37,.88),rgba(3,40,37,.48) 58%,rgba(3,40,37,.12))}.slide-content{position:absolute;z-index:2;left:clamp(28px,6vw,74px);top:50%;transform:translateY(-50%);max-width:680px;padding-right:24px}.slide-content h1{font-size:clamp(40px,6vw,70px)}.slider-controls{position:absolute;z-index:4;left:clamp(28px,6vw,74px);right:26px;bottom:28px;display:flex;justify-content:space-between;align-items:center}.dots{display:flex;gap:8px}.dot{width:11px;height:11px;border-radius:999px;border:1px solid #fff;background:#ffffff55;cursor:pointer;padding:0}.dot.active{width:32px;background:#fff}.arrow-group{display:flex;gap:8px}.arrow{width:42px;height:42px;border-radius:50%;border:1px solid #ffffff99;background:#132f2b99;color:#fff;font-size:21px;cursor:pointer}.grid{max-width:1180px;margin:26px auto 60px;padding:0 20px;display:grid;grid-template-columns:repeat(3,1fr);gap:18px}.tile{background:white;padding:28px;border-radius:20px;border:1px solid #e1eaf0}.tile h3{margin:0 0 10px}.tile p{color:var(--muted);line-height:1.6}.footer{text-align:center;color:var(--muted);padding:24px}@media(max-width:760px){.hero-shell{padding:0 12px}.hero{border-radius:24px}.hero-static{padding:36px 24px}.slider{height:520px}.slide-content{left:24px}.grid{grid-template-columns:1fr;padding:0 12px}.nav{padding:16px}.identity span{max-width:190px}.hero h1,.slide-content h1{font-size:42px}.slider-controls{left:24px;bottom:20px}}
    </style>
</head>
<body>
<nav class="nav">
    <div class="identity">
        @if($school->logo_path)<img class="logo" src="{{ Storage::disk('public')->url($school->logo_path) }}" alt="{{ $school->name }} logo">@endif
        <span>{{ $school->name }}</span>
    </div>
    <a href="{{ route('school.portal.login',$school) }}">View result</a>
</nav>
<main>
    <section class="hero-shell">
        @if($sliderEnabled && $slides->isNotEmpty())
            <div class="hero slider" id="school-slider" data-interval="{{ max(3, min(15, (int) $school->setting('landing.slider_interval_seconds', 6))) * 1000 }}" aria-label="School announcements">
                @foreach($slides as $slide)
                    <article class="slide {{ $loop->first ? 'active' : '' }}" data-index="{{ $loop->index }}" aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
                        <img src="{{ Storage::disk('public')->url($slide->image_path) }}" alt="">
                        <div class="slide-content">
                            <span class="tag">{{ $school->motto ?: 'Learning • Character • Excellence' }}</span>
                            <h1>{{ $slide->title }}</h1>
                            @if($slide->subtitle)<p>{{ $slide->subtitle }}</p>@endif
                            @php
                                $slideUrl = $slide->button_url && \Illuminate\Support\Str::startsWith($slide->button_url, ['https://', 'http://'])
                                    ? $slide->button_url
                                    : route('school.portal.login', $school);
                            @endphp
                            <a class="cta" href="{{ $slideUrl }}">{{ $slide->button_text ?: 'Open student result portal' }} →</a>
                        </div>
                    </article>
                @endforeach
                @if($slides->count() > 1)
                    <div class="slider-controls">
                        <div class="dots" aria-label="Choose slide">
                            @foreach($slides as $slide)
                                <button class="dot {{ $loop->first ? 'active' : '' }}" type="button" data-go="{{ $loop->index }}" aria-label="Show slide {{ $loop->iteration }}"></button>
                            @endforeach
                        </div>
                        <div class="arrow-group">
                            <button class="arrow" type="button" data-prev aria-label="Previous slide">‹</button>
                            <button class="arrow" type="button" data-next aria-label="Next slide">›</button>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <section class="hero hero-static">
                <span class="tag">{{ $school->motto ?: 'Learning • Character • Excellence' }}</span>
                <h1>Welcome to {{ $school->name }}</h1>
                <p>{{ $school->about ?: 'A learning community committed to academic excellence, responsible character, and the success of every student.' }}</p>
                <a class="cta" href="{{ route('school.portal.login',$school) }}">Open student result portal →</a>
            </section>
        @endif
    </section>
    <section class="grid">
        <article class="tile"><h3>Student result portal</h3><p>Students and parents can securely access only results officially released by the school.</p></article>
        <article class="tile"><h3>Verified report cards</h3><p>Every published report has a unique verification code and an immutable compilation snapshot.</p></article>
        <article class="tile"><h3>School contact</h3><p>{{ $school->address ?: 'Address available from the school office.' }}<br>{{ $school->phone }} @if($school->email)<br>{{ $school->email }}@endif</p></article>
    </section>
</main>
<footer class="footer">© {{ now()->year }} {{ $school->name }}</footer>
@if($sliderEnabled && $slides->count() > 1)
<script>
(() => {
    const slider = document.getElementById('school-slider');
    if (!slider) return;
    const slides = [...slider.querySelectorAll('.slide')];
    const dots = [...slider.querySelectorAll('.dot')];
    let index = 0;
    let timer;
    const show = (next) => {
        index = (next + slides.length) % slides.length;
        slides.forEach((slide, i) => {
            const active = i === index;
            slide.classList.toggle('active', active);
            slide.setAttribute('aria-hidden', active ? 'false' : 'true');
        });
        dots.forEach((dot, i) => dot.classList.toggle('active', i === index));
    };
    const start = () => {
        clearInterval(timer);
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            timer = setInterval(() => show(index + 1), Number(slider.dataset.interval || 6000));
        }
    };
    slider.querySelector('[data-prev]')?.addEventListener('click', () => { show(index - 1); start(); });
    slider.querySelector('[data-next]')?.addEventListener('click', () => { show(index + 1); start(); });
    dots.forEach(dot => dot.addEventListener('click', () => { show(Number(dot.dataset.go)); start(); }));
    slider.addEventListener('mouseenter', () => clearInterval(timer));
    slider.addEventListener('mouseleave', start);
    start();
})();
</script>
@endif
</body>
</html>

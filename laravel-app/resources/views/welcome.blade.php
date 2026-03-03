<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Zippd</title>

    @vite(['resources/css/app.css', 'resources/css/welcome.css'])
</head>
<body class="bg-[#f3f3eb] text-[#171717] antialiased">
    <header class="topbar-animate border-b border-black/10">
        <div class="flex w-full items-center justify-between px-5 py-5 md:px-10 md:py-6 lg:px-14">
            <a href="/" class="font-display text-2xl leading-none tracking-tight md:text-[30px]">Zippd</a>

            <div class="flex items-center gap-6">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-full border border-black/20 px-5 py-2.5 text-sm font-semibold uppercase tracking-wide transition hover:border-black hover:bg-black hover:text-white">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-semibold uppercase tracking-wide text-black/75 transition hover:text-black">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="rounded-full bg-black px-5 py-2.5 text-sm font-semibold uppercase tracking-wide text-white transition hover:opacity-90">
                        Create Account
                    </a>
                @endauth

            </div>
        </div>
    </header>

    <main id="home">
        <section class="mx-auto w-[92%] max-w-[1120px] pb-10 pt-12 md:pt-16">
            <h1 class="font-display text-center text-[52px] leading-[0.94] tracking-[-0.03em] text-black sm:text-[72px] md:text-[96px]">
                <span class="hero-line line-1">Shop smart.</span>
                <span class="hero-line line-2"><span class="mark-overlay mark-overlay-pink">Buy easy.</span></span>
                <span class="hero-line line-3">Get it <span class="mark-overlay mark-overlay-mint">delivered.</span></span>
            </h1>

            <div class="hero-frame mt-10 md:mt-12">
                <img
                    src="{{ asset('images/landing/hero-illustration.png') }}"
                    alt="Hero illustration"
                    class="h-full w-full object-cover object-[56%_center]"
                >
            </div>
        </section>

        <section id="features" class="mx-auto grid w-[92%] max-w-[1120px] gap-10 py-8 md:grid-cols-[1fr_360px] md:items-center md:gap-12 md:py-16">
            <div class="reveal-on-scroll" style="--reveal-delay: 80ms;">
                <h2 class="font-display text-[40px] leading-[0.94] tracking-[-0.02em] text-black md:text-[62px]">
                    From sign up to shipping -
                    <br>
                    everything in one place.
                </h2>

                <p class="font-roboto mt-5 max-w-[540px] text-[15px] leading-relaxed text-black/70 md:mt-6 md:text-[17px]">
                    Zippd provides a complete shopping experience:
                    product browsing, cart management,
                    secure checkout, and order tracking.
                </p>
            </div>

            <div class="reveal-on-scroll justify-self-center md:justify-self-end" style="--reveal-delay: 180ms;">
                <div class="h-[260px] w-[260px] overflow-hidden rounded-full ring-1 ring-black/5 md:h-[340px] md:w-[340px]">
                    <img
                        src="{{ asset('images/landing/logo.jpg') }}"
                        alt="Team discussion"
                        class="h-full w-full object-cover object-center"
                    >
                </div>
            </div>
        </section>

        <section id="how" class="mt-6 bg-[#cfe3d0] py-14 md:py-20">
            <div class="reveal-on-scroll mx-auto w-[92%] max-w-[1120px]" style="--reveal-delay: 100ms;">
                <h3 class="font-display text-center text-[48px] leading-[0.92] tracking-[-0.03em] text-black md:text-[84px]">
                    Start your shopping
                    journey today
                </h3>

                <div class="mt-8 flex justify-center">
                    @guest
                        <a href="{{ route('register') }}" class="cta-button inline-flex items-center justify-center rounded-full bg-black px-6 py-4 text-[22px] font-semibold text-white transition hover:opacity-90">
                            Create Account
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="cta-button inline-flex items-center justify-center rounded-full bg-black px-6 py-4 text-[22px] font-semibold text-white transition hover:opacity-90">
                            Go to Dashboard
                        </a>
                    @endguest
                </div>

                <p class="mt-20 text-center text-[11px] text-black/60 md:mt-24">
                    &copy; {{ date('Y') }} Zippd. All rights reserved.
                </p>
            </div>
        </section>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const revealItems = document.querySelectorAll('.reveal-on-scroll');
            if (!('IntersectionObserver' in window)) {
                revealItems.forEach((item) => item.classList.add('is-visible'));
                return;
            }

            const observer = new IntersectionObserver((entries, obs) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        obs.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.18, rootMargin: '0px 0px -40px 0px' });

            revealItems.forEach((item) => observer.observe(item));
        });
    </script>
</body>
</html>

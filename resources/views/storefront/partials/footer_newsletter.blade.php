@php
    $subscribed = session('subscribed');
@endphp
{{-- Email Marketing is a Pro feature — no point collecting signups a free
     tenant has no way to send anything to yet. --}}
@if(($tenant->plan_tier ?? 'free') === 'pro')
    <div class="footer-newsletter">
        @if($subscribed)
            <p class="footer-newsletter-success">🎉 Thanks! You're subscribed to {{ $tenant->name }}'s offers.</p>
        @else
            <form action="{{ route('newsletter.subscribe') }}" method="POST" class="footer-newsletter-form">
                @csrf
                <label for="footer-newsletter-email" class="footer-newsletter-label">Get offers &amp; coupons by email</label>
                <div class="footer-newsletter-fields">
                    <input type="email" id="footer-newsletter-email" name="email" placeholder="Your email address" required class="footer-newsletter-input">
                    <button type="submit" class="footer-newsletter-btn">Subscribe</button>
                </div>
                @error('email')
                    <p class="footer-newsletter-error">{{ $message }}</p>
                @enderror
            </form>
        @endif
    </div>
@endif

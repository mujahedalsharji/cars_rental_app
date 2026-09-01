import './bootstrap';

const mobileMenuButton = document.querySelector('[data-mobile-menu-button]');
const mobileMenu = document.querySelector('[data-mobile-menu]');

mobileMenuButton?.addEventListener('click', () => {
    const isExpanded = mobileMenuButton.getAttribute('aria-expanded') === 'true';
    mobileMenuButton.setAttribute('aria-expanded', String(!isExpanded));
    mobileMenu?.classList.toggle('hidden');
});

document.querySelectorAll('[data-mobile-menu] a').forEach((link) => {
    link.addEventListener('click', () => {
        mobileMenu?.classList.add('hidden');
        mobileMenuButton?.setAttribute('aria-expanded', 'false');
    });
});

document.querySelectorAll('[data-service-carousel]').forEach((carousel) => {
    const viewport = carousel.querySelector('[data-service-carousel-viewport]');
    const cards = Array.from(carousel.querySelectorAll('[data-service-carousel-card]'));
    const indicators = Array.from(carousel.querySelectorAll('[data-service-carousel-indicator]'));
    const nextButton = document.querySelector('[data-service-carousel-next]');
    const previousButton = document.querySelector('[data-service-carousel-previous]');
    const autoplayDelay = Number(carousel.dataset.autoplayMs) || 10000;
    let activeIndex = 0;
    let autoplayTimer;
    let touchStartX;

    if (!viewport || cards.length < 2) {
        nextButton?.classList.add('hidden');
        previousButton?.classList.add('hidden');

        return;
    }

    const updateCarousel = () => {
        cards.forEach((card, index) => {
            let position = index - activeIndex;

            if (position > cards.length / 2) {
                position -= cards.length;
            } else if (position < -cards.length / 2) {
                position += cards.length;
            }

            const isActive = position === 0;
            card.dataset.carouselPosition = Math.abs(position) <= 1 ? String(position) : 'far';
            card.setAttribute('aria-hidden', String(!isActive));
            card.toggleAttribute('inert', !isActive);
        });

        indicators.forEach((indicator, index) => {
            const isActive = index === activeIndex;
            indicator.classList.toggle('w-8', isActive);
            indicator.classList.toggle('bg-gold', isActive);
            indicator.classList.toggle('w-2.5', !isActive);
            indicator.classList.toggle('bg-white/25', !isActive);

            if (isActive) {
                indicator.setAttribute('aria-current', 'true');
            } else {
                indicator.removeAttribute('aria-current');
            }
        });
    };

    const showCard = (requestedIndex) => {
        activeIndex = (requestedIndex + cards.length) % cards.length;
        updateCarousel();
    };

    const stopAutoplay = () => {
        window.clearInterval(autoplayTimer);
    };

    const startAutoplay = () => {
        stopAutoplay();

        if (!document.hidden) {
            autoplayTimer = window.setInterval(() => showCard(activeIndex + 1), autoplayDelay);
        }
    };

    const selectCard = (index) => {
        showCard(index);
        startAutoplay();
    };

    nextButton?.addEventListener('click', () => selectCard(activeIndex + 1));
    previousButton?.addEventListener('click', () => selectCard(activeIndex - 1));

    indicators.forEach((indicator) => {
        indicator.addEventListener('click', () => selectCard(Number(indicator.dataset.serviceCarouselIndicator)));
    });

    viewport.addEventListener('touchstart', (event) => {
        touchStartX = event.touches[0]?.clientX;
        stopAutoplay();
    }, { passive: true });
    viewport.addEventListener('touchend', (event) => {
        const touchEndX = event.changedTouches[0]?.clientX;

        if (touchStartX !== undefined && touchEndX !== undefined && Math.abs(touchEndX - touchStartX) > 45) {
            selectCard(activeIndex + (touchEndX < touchStartX ? 1 : -1));
        } else {
            startAutoplay();
        }

        touchStartX = undefined;
    }, { passive: true });
    carousel.addEventListener('mouseenter', stopAutoplay);
    carousel.addEventListener('mouseleave', startAutoplay);
    carousel.addEventListener('focusin', stopAutoplay);
    carousel.addEventListener('focusout', (event) => {
        if (!carousel.contains(event.relatedTarget)) {
            startAutoplay();
        }
    });
    document.addEventListener('visibilitychange', startAutoplay);

    updateCarousel();
    startAutoplay();
});

const tripNumberUrl = document.querySelector('meta[name="trip-number-url"]')?.content;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

const reserveTripNumber = async () => {
    if (!tripNumberUrl || !csrfToken) {
        throw new Error('Trip number service is not configured.');
    }

    const response = await fetch(tripNumberUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
    });

    if (!response.ok) {
        throw new Error(`Trip number request failed with status ${response.status}.`);
    }

    const payload = await response.json();
    const tripNumber = Number(payload.trip_number);

    if (!Number.isSafeInteger(tripNumber) || tripNumber < 1) {
        throw new Error('Trip number response is invalid.');
    }

    return tripNumber;
};

const openWhatsAppMessage = async (whatsappNumber, buildMessage, trigger) => {
    const normalizedNumber = whatsappNumber?.replace(/\D/g, '');

    if (!normalizedNumber || trigger?.dataset.whatsappPending === 'true') {
        return;
    }

    if (trigger) {
        trigger.dataset.whatsappPending = 'true';
        trigger.setAttribute('aria-busy', 'true');

        if (trigger instanceof HTMLButtonElement) {
            trigger.disabled = true;
        }
    }

    const pendingWindow = window.open('about:blank', '_blank');

    if (pendingWindow) {
        pendingWindow.opener = null;
    }

    try {
        const tripNumber = await reserveTripNumber();
        const message = buildMessage(tripNumber);
        const url = `https://wa.me/${normalizedNumber}?text=${encodeURIComponent(message)}`;

        if (pendingWindow) {
            pendingWindow.location.replace(url);
        } else {
            window.location.assign(url);
        }
    } catch (error) {
        pendingWindow?.close();
        console.error(error);
        window.alert('تعذر إنشاء رقم الرحلة. يرجى المحاولة مرة أخرى.');
    } finally {
        if (trigger) {
            delete trigger.dataset.whatsappPending;
            trigger.removeAttribute('aria-busy');

            if (trigger instanceof HTMLButtonElement) {
                trigger.disabled = false;
            }
        }
    }
};

document.querySelectorAll('[data-whatsapp-message]').forEach((link) => {
    link.addEventListener('click', (event) => {
        const whatsappNumber = link.dataset.whatsappNumber;
        const baseMessage = link.dataset.whatsappMessage;

        if (!whatsappNumber || !baseMessage) {
            return;
        }

        event.preventDefault();
        void openWhatsAppMessage(
            whatsappNumber,
            (tripNumber) => `${baseMessage}\nرقم الرحلة: ${tripNumber}`,
            link,
        );
    });
});

const homeQuoteForm = document.querySelector('[data-home-quote-form]');

homeQuoteForm?.addEventListener('submit', (event) => {
    event.preventDefault();

    const formData = new FormData(homeQuoteForm);
    const pickup = String(formData.get('pickup') || '').trim();
    const destination = String(formData.get('destination') || '').trim();
    const date = String(formData.get('date') || '').trim();
    const service = String(formData.get('service') || '').trim();
    const submitButton = homeQuoteForm.querySelector('[type="submit"]');

    void openWhatsAppMessage(
        homeQuoteForm.dataset.whatsappNumber,
        (tripNumber) => {
            const lines = [
                '🚗 *طلب عرض سعر — فخامة مسافر*',
                '',
                `📋 *نوع الخدمة:* ${service}`,
            ];

            if (pickup) {
                lines.push(`📍 *من:* ${pickup}`);
            }

            if (destination) {
                lines.push(`📍 *إلى:* ${destination}`);
            }

            if (date) {
                lines.push(`📅 *التاريخ:* ${date}`);
            }

            lines.push(`رقم الرحلة: ${tripNumber}`, '', 'أرجو التواصل لتأكيد الحجز.');

            return lines.join('\n');
        },
        submitButton,
    );
});

const bookingForm = document.querySelector('[data-booking-form]');

bookingForm?.querySelectorAll('[data-letters-only]').forEach((input) => {
    input.addEventListener('input', () => {
        input.value = input.value.replace(/[^\p{L}\p{M} ]/gu, '');
    });
});

bookingForm?.querySelectorAll('[data-digits-only]').forEach((input) => {
    input.addEventListener('input', () => {
        input.value = input.value
            .replace(/[٠-٩]/g, (digit) => String('٠١٢٣٤٥٦٧٨٩'.indexOf(digit)))
            .replace(/[۰-۹]/g, (digit) => String('۰۱۲۳۴۵۶۷۸۹'.indexOf(digit)))
            .replace(/\D/g, '')
            .slice(0, 9);
    });
});

bookingForm?.addEventListener('submit', (event) => {
    const whatsappNumber = bookingForm.dataset.whatsappNumber;

    if (!whatsappNumber) {
        return;
    }

    event.preventDefault();

    const formData = new FormData(bookingForm);
    const car = formData.get('car_name') || 'غير محددة';
    const pickup = formData.get('pickup') || 'غير محدد';
    const destination = formData.get('destination') || 'غير محدد';
    const date = formData.get('date') || 'غير محدد';
    const time = formData.get('time') || 'غير محدد';
    const passengers = formData.get('passengers') || 'غير محدد';
    const customerName = formData.get('name') || 'غير محدد';
    const phone = formData.get('phone') || 'غير محدد';
    const submitButton = bookingForm.querySelector('[type="submit"]');

    void openWhatsAppMessage(
        whatsappNumber,
        (tripNumber) => [
            'مرحباً فخامة مسافر، أرغب في طلب رحلة.',
            `الاسم: ${customerName}`,
            `رقم التواصل: ${phone}`,
            `السيارة: ${car}`,
            `من: ${pickup}`,
            `إلى: ${destination}`,
            `التاريخ: ${date}`,
            `الوقت: ${time}`,
            `رقم الرحلة: ${tripNumber}`,
            `عدد الركاب: ${passengers}`,
        ].join('\n'),
        submitButton,
    );
});

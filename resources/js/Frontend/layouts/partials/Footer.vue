<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const year = computed(() => new Date().getFullYear())
const footerLogoSrc = '/images/dezestorelogofooter.png'
const contactNumber = '077 203 0597'
const whatsappNumber = '94772030597'
const address = 'No.14/S Waragashinna, Akurana 20850'

const quickLinks = computed(() => [
  { label: 'Home', href: route('frontend.root') },
  { label: 'Motorcycle Products', href: route('frontend.motorcycle-products.index') },
  { label: 'Electronics', href: route('frontend.tech-products.index') },
  { label: 'Cosmetics', href: route('frontend.cosmetic-products.index') },
  { label: 'Fashion', href: route('frontend.fashion.index') },
  { label: 'Home Needs', href: route('frontend.home-needs.index') },
  { label: 'Contact Us', href: route('frontend.contact-us.index') },
])

const shopLinks = computed(() => [
  { label: 'Today Deals', href: `${route('frontend.root')}#today-deals-section` },
  { label: 'Best Sellers', href: `${route('frontend.root')}#best-sellers-section` },
  { label: 'All Categories', href: `${route('frontend.root')}#category-products-section` },
  { label: 'Shopping Cart', href: route('frontend.cart.index') },
  { label: 'Checkout', href: route('frontend.checkout.index') },
])

const socialLinks = computed(() => [
  {
    label: 'Facebook',
    href: 'https://www.facebook.com/p/DeZe-Storelk-100083468113629/',
  },
  {
    label: 'Instagram',
    href: 'https://www.instagram.com/p/DSuMSBsCFPt/',
  },
  {
    label: 'TikTok',
    href: 'https://www.tiktok.com/@dezestore.lk',
  },
  {
    label: 'WhatsApp',
    href: `https://wa.me/${whatsappNumber}`,
  },
])
</script>

<template>
  <footer class="footer">
    <div class="footer-glow" />

    <div class="footer-container">
      <div class="footer-top">
        <div class="footer-brand">
          <Link :href="route('frontend.root')" class="brand-link" aria-label="DezeStore Home">
            <img
              :src="footerLogoSrc"
              alt="DezeStore"
              class="footer-logo-img"
              width="260"
              height="78"
              loading="lazy"
              decoding="async"
            />
          </Link>

          <p class="brand-text">
            Discover electronics, motorcycle products, cosmetics, fashion, and home essentials with a modern shopping
            experience designed for quality, simplicity, and trust.
          </p>

          <div class="social-links" aria-label="Social media links">
            <a
              v-for="item in socialLinks"
              :key="item.label"
              :href="item.href"
              class="social-link"
              :aria-label="item.label"
              target="_blank"
              rel="noopener noreferrer"
            >
              <svg v-if="item.label === 'Facebook'" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M13.5 21v-7h2.4l.4-3h-2.8V9.1c0-.9.3-1.6 1.6-1.6H16.5V4.8c-.2 0-1-.1-1.9-.1-1.9 0-3.3 1.2-3.3 3.5V11H9v3h2.3v7h2.2z" />
              </svg>
              <svg v-else-if="item.label === 'Instagram'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <rect x="3.5" y="3.5" width="17" height="17" rx="4.5" />
                <circle cx="12" cy="12" r="3.8" />
                <circle cx="17.3" cy="6.7" r="0.9" fill="currentColor" stroke="none" />
              </svg>
              <svg v-else-if="item.label === 'TikTok'" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M14.7 3c.3 1.8 1.4 3.3 3 4.1.9.4 1.8.7 2.8.7v3.1c-1.3 0-2.5-.3-3.6-.8v5.5c0 3.3-2.7 5.9-5.9 5.9S5 18.9 5 15.7s2.7-5.9 5.9-5.9c.3 0 .6 0 .9.1V13a3 3 0 00-.9-.1 2.8 2.8 0 102.8 2.8V3h3z" />
              </svg>
              <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.52 3.48A11.86 11.86 0 0012.04 0C5.52 0 .24 5.28.24 11.8c0 2.08.54 4.12 1.58 5.91L0 24l6.47-1.7a11.76 11.76 0 005.57 1.42h.01c6.52 0 11.8-5.28 11.8-11.8 0-3.15-1.23-6.11-3.33-8.44z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.52 6.99c-.22-.5-.46-.51-.67-.52h-.57c-.2 0-.53.08-.81.38-.28.31-1.07 1.05-1.07 2.56s1.1 2.97 1.25 3.17c.15.2 2.16 3.47 5.34 4.72 2.64 1.03 3.18.83 3.75.78.57-.05 1.85-.75 2.11-1.47.26-.72.26-1.33.18-1.47-.08-.13-.3-.2-.62-.36-.32-.16-1.85-.92-2.13-1.03-.28-.1-.48-.15-.69.16-.2.31-.78 1.03-.96 1.24-.18.2-.36.23-.68.08-.32-.16-1.34-.49-2.56-1.58-.94-.84-1.58-1.87-1.76-2.18-.18-.31-.02-.47.14-.62.14-.13.32-.35.48-.52.16-.18.22-.31.34-.52.11-.21.06-.39-.02-.54-.08-.16-.71-1.78-.98-2.39z" />
              </svg>
            </a>
          </div>
        </div>

        <div class="footer-grid">
          <div class="footer-column">
            <h3 class="footer-title">Quick Links</h3>

            <nav class="footer-links" aria-label="Quick links">
              <Link
                v-for="item in quickLinks"
                :key="item.label"
                :href="item.href"
                class="footer-link"
              >
                {{ item.label }}
              </Link>
            </nav>
          </div>

          <div class="footer-column">
            <h3 class="footer-title">Shop</h3>

            <div class="footer-links">
              <Link
                v-for="item in shopLinks"
                :key="item.label"
                :href="item.href"
                class="footer-link"
              >
                {{ item.label }}
              </Link>
            </div>
          </div>

          <div class="footer-column">
            <h3 class="footer-title">Contact Info</h3>

            <div class="contact-list">
              <div class="contact-item">
                <span class="contact-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M12 21s7-5.686 7-11a7 7 0 10-14 0c0 5.314 7 11 7 11z"
                    />
                    <circle cx="12" cy="10" r="2.5" />
                  </svg>
                </span>
                <p class="contact-text">{{ address }}</p>
              </div>

              <div class="contact-item">
                <span class="contact-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M22 16.92v3a2 2 0 01-2.18 2 19.86 19.86 0 01-8.63-3.07 19.5 19.5 0 01-6-6A19.86 19.86 0 012.08 4.18 2 2 0 014.06 2h3a2 2 0 012 1.72c.12.9.33 1.77.63 2.6a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.48-1.18a2 2 0 012.11-.45c.83.3 1.7.51 2.6.63A2 2 0 0122 16.92z"
                    />
                  </svg>
                </span>
                <a href="tel:0772030597" class="contact-link-text">{{ contactNumber }}</a>
              </div>

              <div class="contact-item">
                <span class="contact-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.52 3.48A11.86 11.86 0 0012.04 0C5.52 0 .24 5.28.24 11.8c0 2.08.54 4.12 1.58 5.91L0 24l6.47-1.7a11.76 11.76 0 005.57 1.42h.01c6.52 0 11.8-5.28 11.8-11.8 0-3.15-1.23-6.11-3.33-8.44z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.52 6.99c-.22-.5-.46-.51-.67-.52h-.57c-.2 0-.53.08-.81.38-.28.31-1.07 1.05-1.07 2.56s1.1 2.97 1.25 3.17c.15.2 2.16 3.47 5.34 4.72 2.64 1.03 3.18.83 3.75.78.57-.05 1.85-.75 2.11-1.47.26-.72.26-1.33.18-1.47-.08-.13-.3-.2-.62-.36-.32-.16-1.85-.92-2.13-1.03-.28-.1-.48-.15-.69.16-.2.31-.78 1.03-.96 1.24-.18.2-.36.23-.68.08-.32-.16-1.34-.49-2.56-1.58-.94-.84-1.58-1.87-1.76-2.18-.18-.31-.02-.47.14-.62.14-.13.32-.35.48-.52.16-.18.22-.31.34-.52.11-.21.06-.39-.02-.54-.08-.16-.71-1.78-.98-2.39z" />
                  </svg>
                </span>
                <a :href="`https://wa.me/${whatsappNumber}`" class="contact-link-text" target="_blank" rel="noopener noreferrer">
                  {{ contactNumber }}
                </a>
              </div>

              <div class="contact-item">
                <span class="contact-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M4 6h16a1 1 0 011 1v10a1 1 0 01-1 1H4a1 1 0 01-1-1V7a1 1 0 011-1z"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M22 7l-10 7L2 7"
                    />
                  </svg>
                </span>
                <a href="mailto:info@dezestore.com" class="contact-link-text">
                  info@dezestore.com
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <div class="footer-bottom-copy">
          <p class="footer-bottom-text">
            Copyright {{ year }} DezeStore. All rights reserved.
          </p>
          <p class="footer-bottom-text">
            Official DezeStore online shopping experience.
          </p>
        </div>
        <p class="footer-bottom-text footer-bottom-credit">
          Designed and developed by Froziolabs
        </p>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.footer {
  position: relative;
  overflow: hidden;
  width: 100%;
  background:
    radial-gradient(circle at top left, rgba(56, 189, 248, 0.14), transparent 30%),
    radial-gradient(circle at top right, rgba(255, 255, 255, 0.06), transparent 26%),
    #000000;
  color: #ffffff;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.footer-glow {
  position: absolute;
  inset: 0;
  pointer-events: none;
  background:
    linear-gradient(180deg, rgba(255, 255, 255, 0.04), transparent 18%),
    linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.025), transparent);
}

.footer-container {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 1280px;
  margin: 0 auto;
  padding: 64px 20px 22px;
}

.footer-top {
  display: grid;
  grid-template-columns: 1.15fr 1.85fr;
  gap: 48px;
  align-items: start;
}

.footer-brand {
  max-width: 460px;
}

.brand-link {
  display: inline-flex;
  align-items: center;
  width: fit-content;
  text-decoration: none;
}

.footer-logo-img {
  display: block;
  width: min(260px, 100%);
  height: auto;
  max-height: 88px;
  object-fit: contain;
  object-position: left center;
  filter: drop-shadow(0 10px 24px rgba(56, 189, 248, 0.15));
  transition: transform 0.28s ease, filter 0.28s ease;
}

.brand-link:hover .footer-logo-img {
  transform: translateY(-2px);
  filter: drop-shadow(0 14px 30px rgba(56, 189, 248, 0.22));
}

.brand-text {
  margin: 20px 0 0;
  font-size: 0.98rem;
  line-height: 1.8;
  color: rgba(255, 255, 255, 0.72);
  max-width: 420px;
}

.social-links {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-top: 24px;
}

.social-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 44px;
  height: 44px;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.05);
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  transition:
    color 0.22s ease,
    background-color 0.22s ease,
    border-color 0.22s ease,
    transform 0.22s ease;
}

.social-link:hover {
  color: #ffffff;
  background: rgba(255, 255, 255, 0.12);
  border-color: rgba(255, 255, 255, 0.24);
  transform: translateY(-2px);
}

.social-link svg {
  width: 20px;
  height: 20px;
}

.footer-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 28px;
}

.footer-column {
  min-width: 0;
  padding: 6px 0;
}

.footer-title {
  margin: 0 0 18px;
  font-size: 0.95rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #ffffff;
}

.footer-links {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.footer-link {
  display: inline-flex;
  align-items: center;
  width: fit-content;
  font-size: 0.96rem;
  font-weight: 500;
  line-height: 1.5;
  color: rgba(255, 255, 255, 0.72);
  text-decoration: none;
  transition: color 0.22s ease, transform 0.22s ease;
}

.footer-link:hover {
  color: #ffffff;
  transform: translateX(3px);
}

.contact-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.contact-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.contact-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 42px;
  height: 42px;
  flex-shrink: 0;
  border-radius: 14px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.05);
  color: #ffffff;
}

.contact-icon svg {
  width: 18px;
  height: 18px;
}

.contact-text,
.contact-link-text {
  margin: 0;
  font-size: 0.95rem;
  line-height: 1.75;
  color: rgba(255, 255, 255, 0.74);
  text-decoration: none;
  transition: color 0.22s ease;
}

.contact-link-text:hover {
  color: #ffffff;
}

.contact-text-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.footer-bottom {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 18px;
  margin-top: 42px;
  padding-top: 22px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.footer-bottom-copy {
  display: flex;
  flex-direction: column;
  gap: 4px;
  align-items: flex-start;
}

.footer-bottom-text {
  margin: 0;
  text-align: left;
  font-size: 0.88rem;
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.58);
}

.footer-bottom-credit {
  text-align: right;
}

@media (max-width: 1080px) {
  .footer-top {
    grid-template-columns: 1fr;
    gap: 36px;
  }

  .footer-brand {
    max-width: none;
  }

  .footer-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 767px) {
  .footer-container {
    padding: 52px 16px 18px;
  }

  .footer-brand {
    text-align: center;
  }

  .brand-link {
    display: flex;
    justify-content: center;
    margin: 0 auto;
  }

  .footer-logo-img {
    width: min(230px, 86vw);
    object-position: center;
  }

  .brand-text {
    margin: 20px auto 0;
    font-size: 0.93rem;
    line-height: 1.75;
  }

  .social-links {
    justify-content: center;
  }

  .footer-grid {
    grid-template-columns: 1fr;
    gap: 24px;
  }

  .footer-column {
    padding: 0;
  }

  .footer-title {
    margin-bottom: 14px;
  }

  .footer-bottom {
    margin-top: 34px;
    padding-top: 18px;
    justify-content: center;
  }

  .footer-bottom-copy {
    align-items: center;
  }

  .footer-bottom-text,
  .footer-bottom-credit {
    text-align: center;
  }
}

@media (max-width: 480px) {
  .footer-container {
    padding-left: 14px;
    padding-right: 14px;
  }

  .footer-logo-img {
    width: min(210px, 84vw);
  }

  .contact-item {
    gap: 10px;
  }

  .contact-icon {
    width: 38px;
    height: 38px;
    border-radius: 12px;
  }

  .contact-text,
  .contact-link-text {
    font-size: 0.91rem;
  }
}
</style>

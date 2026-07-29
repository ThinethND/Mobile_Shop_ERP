<script setup lang="ts">
import AppLayout from '@/Frontend/layouts/AppLayout.vue';
import HomeBanner from './HomeBanner.vue';
// import HomeMainBanner from './HomeMainBanner.vue';
// import Product from './Product.vue';
// import ShoeCategories from './shoecategories.vue';
// import ShoeFeaturedProducts from './ShoeFeaturedProducts.vue';
// import TechCategories from './techcategories.vue';
// import Cosmetics from './Cosmetics.vue'
// import FeaturedProducts from './FeaturedProducts.vue';
// import ThreeDPhone from '../../components/3dphone.vue'
// import PhoneImageShowcase from './PhoneImageShowcase.vue';
// import ThreeDShoe from '../../components/3dshoe.vue'
// import CosmeticImageShowcase from './CosmeticImageShowcase.vue';
// import CosmeticsFeaturedProducts from './CosmeticsFeaturedProducts.vue';
// import HomeQuickNav from './HomeQuickNav.vue';
// import ShoeImageShowcase from './ShoeImageShowcase.vue';
import LazyProductRail from './components/LazyProductRail.vue';
import LazyCategoryProductSection from './components/LazyCategoryProductSection.vue';
import LazyCustomerGallery from './components/LazyCustomerGallery.vue';

defineOptions({
    layout: AppLayout,
});

defineProps<{
    products: Array<{
        id: number | string;
        name: string;
        category_name?: string | null;
        brand_name?: string | null;
        thumbnail_url: string | null;
        hover_image_url: string | null;
        regular_price: number | null;
        display_price: number | null;
        has_discount: boolean;
        discount_label?: string | null;
        is_sold_out: boolean;
        reviews_count?: number;
        reviews_avg_rating?: number | null;
        colors?: Array<{
            id: number | string;
            name: string;
            color_code?: string | null;
            image_url?: string | null;
        }>;
    }>;
    activeCategory?: string | null;
    activeBrand?: string | null;
    search?: string | null;
    categories: Array<{
        id: number | string;
        name: string;
        image_url: string | null;
        status?: string | null;
    }>;
    shoeCategories: Array<{
        id: number | string;
        name: string;
        image_url: string | null;
        status?: string | null;
    }>;
    featuredShoes: Array<{
        id: number | string;
        name: string;
        slug?: string | null;
        brand_name?: string | null;
        thumbnail_url: string | null;
        hover_image_url: string | null;
        currency?: string | null;
        regular_price: number | null;
        sale_price: number | null;
        display_price: number | null;
        has_discount: boolean;
        discount_label?: string | null;
        is_sold_out: boolean;
        status?: string | null;
        stock_status?: string | null;
    }>;
}>();

// const phoneSlides = [
//     {
//         id: 1,
//         title: 'Introducing iPhone 17 Pro Max',
//         description:
//             'Experience a sleek premium design, powerful performance, and a next-level flagship feel built for everyday excellence.',
//         image: '/assets/images/ip171.webp',
//         alt: 'iPhone 17 Pro Max',
//     },
//     {
//         id: 2,
//         title: 'MEMO CX07 Gaming Cooler',
//         description:
//             'Compact RGB cooling with a futuristic design, built to reduce heat and keep your phone performing smoothly during intense gaming sessions.',
//         image: '/assets/images/bacgorund.webp',
//         alt: 'MEMO CX07 Gaming Cooler',
//     },
//     {
//         id: 3,
//         title: 'Meet Samsung S26 Ultra',
//         description:
//             'A bold flagship with a stunning display, refined design, and powerful features made for work, play, and creativity.',
//         image: '/assets/images/s24.webp',
//         alt: 'Samsung S26 Ultra',
//     },
//     {
//         id: 4,
//         title: 'SR 17 Pro Phone Cooler',
//         description:
//             'Powerful phone cooling with a sleek pro design, made to reduce heat and keep your mobile gaming smooth, stable, and comfortable.',
//         image: '/assets/images/phonecoolernew.webp',
//         alt: 'SR 17 Pro Phone Cooler',
//     },
// ];

const shoeSlides = [
    {
        id: 1,
        badge: 'New Arrival',
        title: 'Introducing Adidas Adizero Evo SL EXO',
        description:
            'Lightweight, fast, and built for comfort with a sleek performance-driven design.',
        image: '/assets/images/adizero.webp',
        alt: 'Adidas Adizero Evo SL EXO',
    },
];

const cosmeticSlides = [
    {
        id: 1,
        badge: 'Skin Essential',
        title: 'The Ordinary Niacinamide',
        description:
            'A lightweight everyday serum designed to help visibly reduce excess oil, refine pores, and support a smoother-looking complexion.',
        image: '/assets/images/serum5.webp',
        alt: 'The Ordinary Niacinamide',
    },
    {
        id: 2,
        badge: 'Daily Hydration',
        title: 'CeraVe Moisturizer',
        description:
            'A simple daily moisturizer made to support long-lasting hydration and help keep the skin barrier feeling soft, balanced, and comfortable.',
        image: '/assets/images/ceravemois1.webp',
        alt: 'CeraVe Moisturizer',
    },
    // {
    //   id: 3,
    //   badge: 'Daily Protection',
    //   title: 'CeraVe Suncream',
    //   description:
    //     'An everyday sun protection essential created to help shield skin while maintaining a lightweight, comfortable feel throughout the day.',
    //   image: '/assets/images/ip171.webp',
    //   alt: 'CeraVe Suncream',
    // },
];
</script>

<!--k-->

<template>
    <!-- <HomeMainBanner /> -->

    <HomeBanner />

    <LazyProductRail
        id="today-best-deals-section"
        eyebrow="Limited Picks"
        title="Today's Best Deals For You"
        subtitle="Fresh steals before they disappear."
        endpoint="/home/sections/today-deals"
        :max-items="6"
        :desktop-visible="4"
    />

      <LazyProductRail
        id="best-sellers-section"
        eyebrow="Customer Favorites"
        title="Best Sellers Everyone Wants"
        subtitle="Top picks customers keep choosing."
        endpoint="/home/sections/best-sellers"
        :max-items="6"
        :desktop-visible="4"
    />

    <LazyCategoryProductSection
        id="motorcycle-products-section"
        title="Motorcycle Gear & Parts"
        subtitle="Helmets, parts, and riding essentials."
        endpoint="/home/sections/motorcycle"
        show-all-href="/motorcycle-products"
        tone="motorcycle"
    />

    <LazyCategoryProductSection
        id="electronics-products-section"
        title="Electronics & Gadgets"
        subtitle="Phones, earbuds, and smart gadgets."
        endpoint="/home/sections/electronics"
        show-all-href="/tech-products"
        tone="electronics"
    />

    <LazyCategoryProductSection
        id="cosmetics-products-section"
        title="Beauty & Glow Picks"
        subtitle="Beauty and daily care favourites."
        endpoint="/home/sections/cosmetics"
        show-all-href="/cosmetics"
        tone="cosmetics"
    />

    <LazyCategoryProductSection
        id="fashion-products-section"
        title="Fashion & Accessories"
        subtitle="Bags, shades, and style pieces."
        endpoint="/home/sections/fashion"
        show-all-href="/fashion"
        tone="fashion"
    />

    <LazyCategoryProductSection
        id="home-needs-products-section"
        title="Home Needs & Smart Finds"
        subtitle="Kitchen, storage, decor, and daily finds."
        endpoint="/home/sections/home-needs"
        show-all-href="/home-needs"
        tone="home"
    />

  

    <LazyCustomerGallery />

    <!-- <TechCategories id="mobile-essentials-section" :categories="categories" /> -->

    <!-- <PhoneImageShowcase
        id="phone-showcase-section"
        :slides="phoneSlides"
        height="700px"
        :autoplay-ms="5000"
    /> -->

    <!-- <FeaturedProducts
        id="mobile-trending-section"
        class="home-trending-placement"
        endpoint="/featured-products"
        :active-category="activeCategory"
        :active-brand="activeBrand"
        :search="search"
    /> -->

    <!--
  <ThreeDPhone
    model-path="/models/iphone_17_pro_max.glb"
    height="700px"
  />
  -->

    <!-- <FeaturedProducts
        endpoint="/featured-products"
        :active-category="activeCategory"
        :active-brand="activeBrand"
        :search="search"
    /> -->

    <!-- <Product
        :products="products"
        :categories="categories"
        :activeCategory="activeCategory"
    /> -->

    <!-- <ShoeCategories id="shoe-categories-section" :categories="shoeCategories" /> -->

    <!--
  <ThreeDShoe
    model-path="/models/air_jordan_1.glb"
    height="680px"
  />
  -->

    <!-- <ShoeImageShowcase
        :slides="shoeSlides"
        height="680px"
        :autoplay-ms="5000"
    /> -->

    <!-- <ShoeFeaturedProducts
        id="shoe-featured-products-section"
        :products="featuredShoes"
    /> -->

    <!-- <CosmeticImageShowcase
        id="cosmetics-section"
        :slides="cosmeticSlides"
        height="700px"
        :autoplay-ms="5000"
    /> -->

    <!-- <CosmeticsFeaturedProducts /> -->

    <!-- <Cosmetics /> -->

    <!-- <CardSwiper /> -->

    <!-- <HomeQuickNav /> -->
</template>

<style scoped>
.home-trending-placement {
    margin-top: clamp(26px, 4vw, 56px);
    margin-bottom: clamp(28px, 5vw, 68px);
    scroll-margin-top: 112px;
}

@media (max-width: 640px) {
    .home-trending-placement {
        margin-top: 26px;
        margin-bottom: 44px;
    }
}
</style>

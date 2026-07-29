<script setup lang="ts">
import NavMain from '@/Backend/components/NavMain.vue';
import NavUser from '@/Backend/components/NavUser.vue';
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/Backend/components/ui/sidebar';
import { NavItemWithSubmenu } from '@/Backend/types';
import { Link } from '@inertiajs/vue3';
import {
  LayoutDashboard,
  Package,
  FolderKanban,
  CreditCard,
} from 'lucide-vue-next';

import AppLogo from './AppLogo.vue';
import { route } from 'ziggy-js';

const mainNavItems: NavItemWithSubmenu[] = [
  {
    title: 'Dashboard',
    href: route('dashboard'),
    icon: LayoutDashboard,
  },

  {
    title: 'Electronics & Gadgets',
    href: '#',
    icon: Package,
    submenu: [
      {
        title: 'Products',
        href: route('products.index'),
      },
      {
        title: 'Categories',
        href: route('categories.index'),
      },
      {
        title: 'Brands',
        href: route('brands.index'),
      },
      {
        title: 'Ram Options',
        href: route('ram.index'),
      },
      {
        title: 'Storage Options',
        href: route('storage.index'),
      },
      {
        title: 'Color Options',
        href: route('colors.index'),
      },
      {
        title: 'Warranty Options',
        href: route('warranty.index'),
      },
      {
        title: 'Product Reviews',
        href: route('product-reviews.index'),
      },
    ],
  },
  // Shoe Products are intentionally hidden from the admin navigation for the current catalog.
  {
    title: 'Cosmetics',
    href: '#',
    icon: Package,
    submenu: [
      {
        title: 'Cosmetic Products',
        href: route('admin.cosmetics.products.index'),
      },
      {
        title: 'Cosmetic Brands',
        href: route('admin.cosmetics.brands.index'),
      },
      {
        title: 'Cosmetic Categories',
        href: route('admin.cosmetics.categories.index'),
      },
      {
        title: 'Product Types',
        href: route('admin.cosmetics.product-types.index'),
      },
      {
        title: 'Sizes / Volume',
        href: route('admin.cosmetics.sizes-volume.index'),
      },
      {
        title: 'Country of Origin',
        href: route('admin.cosmetics.countries-origin.index'),
      },
      {
        title: 'Product Reviews',
        href: route('admin.cosmetics.product-reviews.index'),
      },
    ],
  },
  {
    title: 'Home Needs',
    href: '#',
    icon: Package,
    submenu: [
      {
        title: 'Home Need Products',
        href: route('admin.home-needs.products.index'),
      },
      {
        title: 'Brands',
        href: route('admin.home-needs.brands.index'),
      },
      {
        title: 'Warranty Options',
        href: route('warranty.index'),
      },
      {
        title: 'Categories',
        href: route('admin.home-needs.categories.index'),
      },
    ],
  },
  {
    title: 'Fashion & Accessories',
    href: '#',
    icon: Package,
    submenu: [
      {
        title: 'Fashion Products',
        href: route('admin.fashion.products.index'),
      },
      {
        title: 'Brands',
        href: route('admin.fashion.brands.index'),
      },
      {
        title: 'Categories',
        href: route('admin.fashion.categories.index'),
      },
      {
        title: 'Product Types',
        href: route('admin.fashion.product-types.index'),
      },
      {
        title: 'Warranty Options',
        href: route('warranty.index'),
      },
    ],
  },
  {
    title: 'Motorcycle Products',
    href: '#',
    icon: Package,
    submenu: [
      {
        title: 'Products',
        href: route('admin.motorcycles.products.index'),
      },
      {
        title: 'Warranty Options',
        href: route('warranty.index'),
      },
      {
        title: 'Product Categories',
        href: route('admin.motorcycles.categories.index'),
      },
      {
        title: 'Helmet Brands',
        href: route('admin.motorcycles.helmet-brands.index'),
      },
      {
        title: 'Bike Brands & Models',
        href: route('admin.motorcycles.bike-models.index'),
      },
      {
        title: 'Product Options',
        href: route('admin.motorcycles.options.index'),
      },
      {
        title: 'Product Reviews',
        href: route('admin.motorcycles.product-reviews.index'),
      },
    ],
  },
  {
    title: 'Other CMS',
    href: route('customer-gallery.index'),
    icon: FolderKanban,
    submenu: [
      // {
      //   title: 'Home Banners',
      //   href: route('homebanners.index'),
      // },
      {
        title: 'Our Customers',
        href: route('customer-gallery.index'),
      },
      {
        title: 'Delivery Charges',
        href: route('delivery-charges.edit'),
      },
    ],
  },

  {
    title: 'Koko Pay',
    href: route('koko-pay.edit'),
    icon: CreditCard,
  },

  {
    title: 'Orders',
    href: route('invoices.index'),
    icon: FolderKanban,
  },
];

</script>

<template>
  <Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton size="lg" as-child>
            <Link :href="route('dashboard')">
              <AppLogo />
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
      <NavMain :items="mainNavItems" />
    </SidebarContent>

    <SidebarFooter>
      <NavUser />
    </SidebarFooter>
  </Sidebar>

  <slot />
</template>

import { InertiaLinkProps } from '@inertiajs/vue3'
import { clsx, type ClassValue } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

/**
 * Normalize both the link and current route for proper comparison.
 * Works with absolute URLs, relative paths, and trailing slashes.
 */
export function urlIsActive(
  urlToCheck?: InertiaLinkProps['href'],
  currentUrl?: string
): boolean {
  const normalize = (url: string): string => {
    try {
      // Remove domain if absolute and ensure only pathname remains
      const path = new URL(url, window.location.origin).pathname
      return path.replace(/\/+$/, '') || '/'
    } catch {
      // Fallback for relative URLs
      return url.replace(/\/+$/, '') || '/'
    }
  }

  const targetPath = normalize(toUrl(urlToCheck) || '')
  const currentPath = normalize(currentUrl || '')

  // ✅ Exact match or starts-with (so /users matches /users/create)
  return currentPath === targetPath || currentPath.startsWith(targetPath + '/')
}

export function toUrl(href: InertiaLinkProps['href']): string {
  return typeof href === 'string' ? href : href?.url || ''
}

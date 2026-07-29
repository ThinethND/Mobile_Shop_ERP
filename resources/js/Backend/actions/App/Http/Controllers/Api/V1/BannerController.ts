import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\V1\BannerController::getBanners
 * @see app/Http/Controllers/Api/V1/BannerController.php:14
 * @route '/api/v1/banners/get-banners'
 */
export const getBanners = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getBanners.url(options),
    method: 'get',
})

getBanners.definition = {
    methods: ["get","head"],
    url: '/api/v1/banners/get-banners',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\BannerController::getBanners
 * @see app/Http/Controllers/Api/V1/BannerController.php:14
 * @route '/api/v1/banners/get-banners'
 */
getBanners.url = (options?: RouteQueryOptions) => {
    return getBanners.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\BannerController::getBanners
 * @see app/Http/Controllers/Api/V1/BannerController.php:14
 * @route '/api/v1/banners/get-banners'
 */
getBanners.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getBanners.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\BannerController::getBanners
 * @see app/Http/Controllers/Api/V1/BannerController.php:14
 * @route '/api/v1/banners/get-banners'
 */
getBanners.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getBanners.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\BannerController::getBanners
 * @see app/Http/Controllers/Api/V1/BannerController.php:14
 * @route '/api/v1/banners/get-banners'
 */
    const getBannersForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: getBanners.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\BannerController::getBanners
 * @see app/Http/Controllers/Api/V1/BannerController.php:14
 * @route '/api/v1/banners/get-banners'
 */
        getBannersForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getBanners.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\BannerController::getBanners
 * @see app/Http/Controllers/Api/V1/BannerController.php:14
 * @route '/api/v1/banners/get-banners'
 */
        getBannersForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getBanners.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    getBanners.form = getBannersForm
const BannerController = { getBanners }

export default BannerController

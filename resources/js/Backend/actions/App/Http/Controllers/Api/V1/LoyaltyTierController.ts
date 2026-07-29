import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\V1\LoyaltyTierController::fetchTiers
 * @see app/Http/Controllers/Api/V1/LoyaltyTierController.php:19
 * @route '/api/v1/loyalty/get-tiers'
 */
export const fetchTiers = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: fetchTiers.url(options),
    method: 'get',
})

fetchTiers.definition = {
    methods: ["get","head"],
    url: '/api/v1/loyalty/get-tiers',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\LoyaltyTierController::fetchTiers
 * @see app/Http/Controllers/Api/V1/LoyaltyTierController.php:19
 * @route '/api/v1/loyalty/get-tiers'
 */
fetchTiers.url = (options?: RouteQueryOptions) => {
    return fetchTiers.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\LoyaltyTierController::fetchTiers
 * @see app/Http/Controllers/Api/V1/LoyaltyTierController.php:19
 * @route '/api/v1/loyalty/get-tiers'
 */
fetchTiers.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: fetchTiers.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\LoyaltyTierController::fetchTiers
 * @see app/Http/Controllers/Api/V1/LoyaltyTierController.php:19
 * @route '/api/v1/loyalty/get-tiers'
 */
fetchTiers.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: fetchTiers.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\LoyaltyTierController::fetchTiers
 * @see app/Http/Controllers/Api/V1/LoyaltyTierController.php:19
 * @route '/api/v1/loyalty/get-tiers'
 */
    const fetchTiersForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: fetchTiers.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\LoyaltyTierController::fetchTiers
 * @see app/Http/Controllers/Api/V1/LoyaltyTierController.php:19
 * @route '/api/v1/loyalty/get-tiers'
 */
        fetchTiersForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: fetchTiers.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\LoyaltyTierController::fetchTiers
 * @see app/Http/Controllers/Api/V1/LoyaltyTierController.php:19
 * @route '/api/v1/loyalty/get-tiers'
 */
        fetchTiersForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: fetchTiers.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    fetchTiers.form = fetchTiersForm
const LoyaltyTierController = { fetchTiers }

export default LoyaltyTierController

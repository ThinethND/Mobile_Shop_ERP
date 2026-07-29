import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\V1\DiscountController::getDiscounts
 * @see app/Http/Controllers/Api/V1/DiscountController.php:14
 * @route '/api/v1/discounts/get-discounts'
 */
export const getDiscounts = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getDiscounts.url(options),
    method: 'get',
})

getDiscounts.definition = {
    methods: ["get","head"],
    url: '/api/v1/discounts/get-discounts',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\DiscountController::getDiscounts
 * @see app/Http/Controllers/Api/V1/DiscountController.php:14
 * @route '/api/v1/discounts/get-discounts'
 */
getDiscounts.url = (options?: RouteQueryOptions) => {
    return getDiscounts.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\DiscountController::getDiscounts
 * @see app/Http/Controllers/Api/V1/DiscountController.php:14
 * @route '/api/v1/discounts/get-discounts'
 */
getDiscounts.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getDiscounts.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\DiscountController::getDiscounts
 * @see app/Http/Controllers/Api/V1/DiscountController.php:14
 * @route '/api/v1/discounts/get-discounts'
 */
getDiscounts.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getDiscounts.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\DiscountController::getDiscounts
 * @see app/Http/Controllers/Api/V1/DiscountController.php:14
 * @route '/api/v1/discounts/get-discounts'
 */
    const getDiscountsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: getDiscounts.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\DiscountController::getDiscounts
 * @see app/Http/Controllers/Api/V1/DiscountController.php:14
 * @route '/api/v1/discounts/get-discounts'
 */
        getDiscountsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getDiscounts.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\DiscountController::getDiscounts
 * @see app/Http/Controllers/Api/V1/DiscountController.php:14
 * @route '/api/v1/discounts/get-discounts'
 */
        getDiscountsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getDiscounts.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    getDiscounts.form = getDiscountsForm
const DiscountController = { getDiscounts }

export default DiscountController

import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\V1\ServiceController::getServiceCategories
 * @see app/Http/Controllers/Api/V1/ServiceController.php:58
 * @route '/api/v1/services/get-service-categories'
 */
export const getServiceCategories = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getServiceCategories.url(options),
    method: 'get',
})

getServiceCategories.definition = {
    methods: ["get","head"],
    url: '/api/v1/services/get-service-categories',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\ServiceController::getServiceCategories
 * @see app/Http/Controllers/Api/V1/ServiceController.php:58
 * @route '/api/v1/services/get-service-categories'
 */
getServiceCategories.url = (options?: RouteQueryOptions) => {
    return getServiceCategories.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\ServiceController::getServiceCategories
 * @see app/Http/Controllers/Api/V1/ServiceController.php:58
 * @route '/api/v1/services/get-service-categories'
 */
getServiceCategories.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getServiceCategories.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\ServiceController::getServiceCategories
 * @see app/Http/Controllers/Api/V1/ServiceController.php:58
 * @route '/api/v1/services/get-service-categories'
 */
getServiceCategories.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getServiceCategories.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\ServiceController::getServiceCategories
 * @see app/Http/Controllers/Api/V1/ServiceController.php:58
 * @route '/api/v1/services/get-service-categories'
 */
    const getServiceCategoriesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: getServiceCategories.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\ServiceController::getServiceCategories
 * @see app/Http/Controllers/Api/V1/ServiceController.php:58
 * @route '/api/v1/services/get-service-categories'
 */
        getServiceCategoriesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getServiceCategories.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\ServiceController::getServiceCategories
 * @see app/Http/Controllers/Api/V1/ServiceController.php:58
 * @route '/api/v1/services/get-service-categories'
 */
        getServiceCategoriesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getServiceCategories.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    getServiceCategories.form = getServiceCategoriesForm
/**
* @see \App\Http\Controllers\Api\V1\ServiceController::getStaffWithServicesWithCategories
 * @see app/Http/Controllers/Api/V1/ServiceController.php:26
 * @route '/api/v1/services/get-services'
 */
export const getStaffWithServicesWithCategories = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: getStaffWithServicesWithCategories.url(options),
    method: 'post',
})

getStaffWithServicesWithCategories.definition = {
    methods: ["post"],
    url: '/api/v1/services/get-services',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\ServiceController::getStaffWithServicesWithCategories
 * @see app/Http/Controllers/Api/V1/ServiceController.php:26
 * @route '/api/v1/services/get-services'
 */
getStaffWithServicesWithCategories.url = (options?: RouteQueryOptions) => {
    return getStaffWithServicesWithCategories.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\ServiceController::getStaffWithServicesWithCategories
 * @see app/Http/Controllers/Api/V1/ServiceController.php:26
 * @route '/api/v1/services/get-services'
 */
getStaffWithServicesWithCategories.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: getStaffWithServicesWithCategories.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\ServiceController::getStaffWithServicesWithCategories
 * @see app/Http/Controllers/Api/V1/ServiceController.php:26
 * @route '/api/v1/services/get-services'
 */
    const getStaffWithServicesWithCategoriesForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: getStaffWithServicesWithCategories.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\ServiceController::getStaffWithServicesWithCategories
 * @see app/Http/Controllers/Api/V1/ServiceController.php:26
 * @route '/api/v1/services/get-services'
 */
        getStaffWithServicesWithCategoriesForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: getStaffWithServicesWithCategories.url(options),
            method: 'post',
        })
    
    getStaffWithServicesWithCategories.form = getStaffWithServicesWithCategoriesForm
const ServiceController = { getServiceCategories, getStaffWithServicesWithCategories }

export default ServiceController

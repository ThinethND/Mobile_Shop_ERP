import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\V1\CountryCodeController::getCountryCodes
 * @see app/Http/Controllers/Api/V1/CountryCodeController.php:14
 * @route '/api/v1/countries/get-country-codes'
 */
export const getCountryCodes = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getCountryCodes.url(options),
    method: 'get',
})

getCountryCodes.definition = {
    methods: ["get","head"],
    url: '/api/v1/countries/get-country-codes',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\CountryCodeController::getCountryCodes
 * @see app/Http/Controllers/Api/V1/CountryCodeController.php:14
 * @route '/api/v1/countries/get-country-codes'
 */
getCountryCodes.url = (options?: RouteQueryOptions) => {
    return getCountryCodes.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\CountryCodeController::getCountryCodes
 * @see app/Http/Controllers/Api/V1/CountryCodeController.php:14
 * @route '/api/v1/countries/get-country-codes'
 */
getCountryCodes.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getCountryCodes.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\CountryCodeController::getCountryCodes
 * @see app/Http/Controllers/Api/V1/CountryCodeController.php:14
 * @route '/api/v1/countries/get-country-codes'
 */
getCountryCodes.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getCountryCodes.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\CountryCodeController::getCountryCodes
 * @see app/Http/Controllers/Api/V1/CountryCodeController.php:14
 * @route '/api/v1/countries/get-country-codes'
 */
    const getCountryCodesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: getCountryCodes.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\CountryCodeController::getCountryCodes
 * @see app/Http/Controllers/Api/V1/CountryCodeController.php:14
 * @route '/api/v1/countries/get-country-codes'
 */
        getCountryCodesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getCountryCodes.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\CountryCodeController::getCountryCodes
 * @see app/Http/Controllers/Api/V1/CountryCodeController.php:14
 * @route '/api/v1/countries/get-country-codes'
 */
        getCountryCodesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getCountryCodes.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    getCountryCodes.form = getCountryCodesForm
const CountryCodeController = { getCountryCodes }

export default CountryCodeController

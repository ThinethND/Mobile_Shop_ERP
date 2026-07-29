import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\SmsGatewayController::index
 * @see app/Http/Controllers/SmsGatewayController.php:14
 * @route '/sms-gateway'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/sms-gateway',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SmsGatewayController::index
 * @see app/Http/Controllers/SmsGatewayController.php:14
 * @route '/sms-gateway'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SmsGatewayController::index
 * @see app/Http/Controllers/SmsGatewayController.php:14
 * @route '/sms-gateway'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\SmsGatewayController::index
 * @see app/Http/Controllers/SmsGatewayController.php:14
 * @route '/sms-gateway'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\SmsGatewayController::index
 * @see app/Http/Controllers/SmsGatewayController.php:14
 * @route '/sms-gateway'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\SmsGatewayController::index
 * @see app/Http/Controllers/SmsGatewayController.php:14
 * @route '/sms-gateway'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\SmsGatewayController::index
 * @see app/Http/Controllers/SmsGatewayController.php:14
 * @route '/sms-gateway'
 */
        indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
/**
* @see \App\Http\Controllers\SmsGatewayController::storeOrUpdate
 * @see app/Http/Controllers/SmsGatewayController.php:25
 * @route '/sms-gateway/store-or-update'
 */
export const storeOrUpdate = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeOrUpdate.url(options),
    method: 'post',
})

storeOrUpdate.definition = {
    methods: ["post"],
    url: '/sms-gateway/store-or-update',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SmsGatewayController::storeOrUpdate
 * @see app/Http/Controllers/SmsGatewayController.php:25
 * @route '/sms-gateway/store-or-update'
 */
storeOrUpdate.url = (options?: RouteQueryOptions) => {
    return storeOrUpdate.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SmsGatewayController::storeOrUpdate
 * @see app/Http/Controllers/SmsGatewayController.php:25
 * @route '/sms-gateway/store-or-update'
 */
storeOrUpdate.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeOrUpdate.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\SmsGatewayController::storeOrUpdate
 * @see app/Http/Controllers/SmsGatewayController.php:25
 * @route '/sms-gateway/store-or-update'
 */
    const storeOrUpdateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storeOrUpdate.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\SmsGatewayController::storeOrUpdate
 * @see app/Http/Controllers/SmsGatewayController.php:25
 * @route '/sms-gateway/store-or-update'
 */
        storeOrUpdateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storeOrUpdate.url(options),
            method: 'post',
        })
    
    storeOrUpdate.form = storeOrUpdateForm
/**
* @see \App\Http\Controllers\SmsGatewayController::testSMS
 * @see app/Http/Controllers/SmsGatewayController.php:45
 * @route '/sms-gateway/test'
 */
export const testSMS = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: testSMS.url(options),
    method: 'post',
})

testSMS.definition = {
    methods: ["post"],
    url: '/sms-gateway/test',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SmsGatewayController::testSMS
 * @see app/Http/Controllers/SmsGatewayController.php:45
 * @route '/sms-gateway/test'
 */
testSMS.url = (options?: RouteQueryOptions) => {
    return testSMS.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SmsGatewayController::testSMS
 * @see app/Http/Controllers/SmsGatewayController.php:45
 * @route '/sms-gateway/test'
 */
testSMS.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: testSMS.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\SmsGatewayController::testSMS
 * @see app/Http/Controllers/SmsGatewayController.php:45
 * @route '/sms-gateway/test'
 */
    const testSMSForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: testSMS.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\SmsGatewayController::testSMS
 * @see app/Http/Controllers/SmsGatewayController.php:45
 * @route '/sms-gateway/test'
 */
        testSMSForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: testSMS.url(options),
            method: 'post',
        })
    
    testSMS.form = testSMSForm
const SmsGatewayController = { index, storeOrUpdate, testSMS }

export default SmsGatewayController

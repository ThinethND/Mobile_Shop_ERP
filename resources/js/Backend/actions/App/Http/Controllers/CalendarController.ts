import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\CalendarController::index
 * @see app/Http/Controllers/CalendarController.php:20
 * @route '/calendar'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/calendar',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\CalendarController::index
 * @see app/Http/Controllers/CalendarController.php:20
 * @route '/calendar'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CalendarController::index
 * @see app/Http/Controllers/CalendarController.php:20
 * @route '/calendar'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\CalendarController::index
 * @see app/Http/Controllers/CalendarController.php:20
 * @route '/calendar'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\CalendarController::index
 * @see app/Http/Controllers/CalendarController.php:20
 * @route '/calendar'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\CalendarController::index
 * @see app/Http/Controllers/CalendarController.php:20
 * @route '/calendar'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\CalendarController::index
 * @see app/Http/Controllers/CalendarController.php:20
 * @route '/calendar'
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
* @see \App\Http\Controllers\CalendarController::staffdata
 * @see app/Http/Controllers/CalendarController.php:0
 * @route '/calendar/data'
 */
export const staffdata = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: staffdata.url(options),
    method: 'get',
})

staffdata.definition = {
    methods: ["get","head"],
    url: '/calendar/data',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\CalendarController::staffdata
 * @see app/Http/Controllers/CalendarController.php:0
 * @route '/calendar/data'
 */
staffdata.url = (options?: RouteQueryOptions) => {
    return staffdata.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CalendarController::staffdata
 * @see app/Http/Controllers/CalendarController.php:0
 * @route '/calendar/data'
 */
staffdata.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: staffdata.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\CalendarController::staffdata
 * @see app/Http/Controllers/CalendarController.php:0
 * @route '/calendar/data'
 */
staffdata.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: staffdata.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\CalendarController::staffdata
 * @see app/Http/Controllers/CalendarController.php:0
 * @route '/calendar/data'
 */
    const staffdataForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: staffdata.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\CalendarController::staffdata
 * @see app/Http/Controllers/CalendarController.php:0
 * @route '/calendar/data'
 */
        staffdataForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: staffdata.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\CalendarController::staffdata
 * @see app/Http/Controllers/CalendarController.php:0
 * @route '/calendar/data'
 */
        staffdataForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: staffdata.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    staffdata.form = staffdataForm
/**
* @see \App\Http\Controllers\CalendarController::servicesdata
 * @see app/Http/Controllers/CalendarController.php:300
 * @route '/calendar/servicesdata'
 */
export const servicesdata = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: servicesdata.url(options),
    method: 'get',
})

servicesdata.definition = {
    methods: ["get","head"],
    url: '/calendar/servicesdata',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\CalendarController::servicesdata
 * @see app/Http/Controllers/CalendarController.php:300
 * @route '/calendar/servicesdata'
 */
servicesdata.url = (options?: RouteQueryOptions) => {
    return servicesdata.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\CalendarController::servicesdata
 * @see app/Http/Controllers/CalendarController.php:300
 * @route '/calendar/servicesdata'
 */
servicesdata.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: servicesdata.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\CalendarController::servicesdata
 * @see app/Http/Controllers/CalendarController.php:300
 * @route '/calendar/servicesdata'
 */
servicesdata.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: servicesdata.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\CalendarController::servicesdata
 * @see app/Http/Controllers/CalendarController.php:300
 * @route '/calendar/servicesdata'
 */
    const servicesdataForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: servicesdata.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\CalendarController::servicesdata
 * @see app/Http/Controllers/CalendarController.php:300
 * @route '/calendar/servicesdata'
 */
        servicesdataForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: servicesdata.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\CalendarController::servicesdata
 * @see app/Http/Controllers/CalendarController.php:300
 * @route '/calendar/servicesdata'
 */
        servicesdataForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: servicesdata.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    servicesdata.form = servicesdataForm
const CalendarController = { index, staffdata, servicesdata }

export default CalendarController

import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\V1\BookingsController::getBookings
 * @see app/Http/Controllers/Api/V1/BookingsController.php:25
 * @route '/api/v1/bookings/get-bookings'
 */
export const getBookings = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getBookings.url(options),
    method: 'get',
})

getBookings.definition = {
    methods: ["get","head"],
    url: '/api/v1/bookings/get-bookings',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\BookingsController::getBookings
 * @see app/Http/Controllers/Api/V1/BookingsController.php:25
 * @route '/api/v1/bookings/get-bookings'
 */
getBookings.url = (options?: RouteQueryOptions) => {
    return getBookings.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\BookingsController::getBookings
 * @see app/Http/Controllers/Api/V1/BookingsController.php:25
 * @route '/api/v1/bookings/get-bookings'
 */
getBookings.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getBookings.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\BookingsController::getBookings
 * @see app/Http/Controllers/Api/V1/BookingsController.php:25
 * @route '/api/v1/bookings/get-bookings'
 */
getBookings.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getBookings.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\BookingsController::getBookings
 * @see app/Http/Controllers/Api/V1/BookingsController.php:25
 * @route '/api/v1/bookings/get-bookings'
 */
    const getBookingsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: getBookings.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\BookingsController::getBookings
 * @see app/Http/Controllers/Api/V1/BookingsController.php:25
 * @route '/api/v1/bookings/get-bookings'
 */
        getBookingsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getBookings.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\BookingsController::getBookings
 * @see app/Http/Controllers/Api/V1/BookingsController.php:25
 * @route '/api/v1/bookings/get-bookings'
 */
        getBookingsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getBookings.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    getBookings.form = getBookingsForm
/**
* @see \App\Http\Controllers\Api\V1\BookingsController::createBooking
 * @see app/Http/Controllers/Api/V1/BookingsController.php:105
 * @route '/api/v1/bookings/create-booking'
 */
export const createBooking = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: createBooking.url(options),
    method: 'post',
})

createBooking.definition = {
    methods: ["post"],
    url: '/api/v1/bookings/create-booking',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\BookingsController::createBooking
 * @see app/Http/Controllers/Api/V1/BookingsController.php:105
 * @route '/api/v1/bookings/create-booking'
 */
createBooking.url = (options?: RouteQueryOptions) => {
    return createBooking.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\BookingsController::createBooking
 * @see app/Http/Controllers/Api/V1/BookingsController.php:105
 * @route '/api/v1/bookings/create-booking'
 */
createBooking.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: createBooking.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\BookingsController::createBooking
 * @see app/Http/Controllers/Api/V1/BookingsController.php:105
 * @route '/api/v1/bookings/create-booking'
 */
    const createBookingForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: createBooking.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\BookingsController::createBooking
 * @see app/Http/Controllers/Api/V1/BookingsController.php:105
 * @route '/api/v1/bookings/create-booking'
 */
        createBookingForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: createBooking.url(options),
            method: 'post',
        })
    
    createBooking.form = createBookingForm
/**
* @see \App\Http\Controllers\Api\V1\BookingsController::updateBooking
 * @see app/Http/Controllers/Api/V1/BookingsController.php:213
 * @route '/api/v1/bookings/update-booking'
 */
export const updateBooking = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateBooking.url(options),
    method: 'put',
})

updateBooking.definition = {
    methods: ["put"],
    url: '/api/v1/bookings/update-booking',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Api\V1\BookingsController::updateBooking
 * @see app/Http/Controllers/Api/V1/BookingsController.php:213
 * @route '/api/v1/bookings/update-booking'
 */
updateBooking.url = (options?: RouteQueryOptions) => {
    return updateBooking.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\BookingsController::updateBooking
 * @see app/Http/Controllers/Api/V1/BookingsController.php:213
 * @route '/api/v1/bookings/update-booking'
 */
updateBooking.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateBooking.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Api\V1\BookingsController::updateBooking
 * @see app/Http/Controllers/Api/V1/BookingsController.php:213
 * @route '/api/v1/bookings/update-booking'
 */
    const updateBookingForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateBooking.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\BookingsController::updateBooking
 * @see app/Http/Controllers/Api/V1/BookingsController.php:213
 * @route '/api/v1/bookings/update-booking'
 */
        updateBookingForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateBooking.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateBooking.form = updateBookingForm
const BookingsController = { getBookings, createBooking, updateBooking }

export default BookingsController

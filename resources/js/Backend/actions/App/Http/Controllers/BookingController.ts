import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\BookingController::store
 * @see app/Http/Controllers/BookingController.php:167
 * @route '/bookings'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/bookings',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\BookingController::store
 * @see app/Http/Controllers/BookingController.php:167
 * @route '/bookings'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::store
 * @see app/Http/Controllers/BookingController.php:167
 * @route '/bookings'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\BookingController::store
 * @see app/Http/Controllers/BookingController.php:167
 * @route '/bookings'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\BookingController::store
 * @see app/Http/Controllers/BookingController.php:167
 * @route '/bookings'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\BookingController::index
 * @see app/Http/Controllers/BookingController.php:30
 * @route '/bookings'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/bookings',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BookingController::index
 * @see app/Http/Controllers/BookingController.php:30
 * @route '/bookings'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::index
 * @see app/Http/Controllers/BookingController.php:30
 * @route '/bookings'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BookingController::index
 * @see app/Http/Controllers/BookingController.php:30
 * @route '/bookings'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BookingController::index
 * @see app/Http/Controllers/BookingController.php:30
 * @route '/bookings'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BookingController::index
 * @see app/Http/Controllers/BookingController.php:30
 * @route '/bookings'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BookingController::index
 * @see app/Http/Controllers/BookingController.php:30
 * @route '/bookings'
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
* @see \App\Http\Controllers\BookingController::getdata
 * @see app/Http/Controllers/BookingController.php:36
 * @route '/getdata'
 */
export const getdata = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getdata.url(options),
    method: 'get',
})

getdata.definition = {
    methods: ["get","head"],
    url: '/getdata',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BookingController::getdata
 * @see app/Http/Controllers/BookingController.php:36
 * @route '/getdata'
 */
getdata.url = (options?: RouteQueryOptions) => {
    return getdata.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::getdata
 * @see app/Http/Controllers/BookingController.php:36
 * @route '/getdata'
 */
getdata.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getdata.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BookingController::getdata
 * @see app/Http/Controllers/BookingController.php:36
 * @route '/getdata'
 */
getdata.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getdata.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BookingController::getdata
 * @see app/Http/Controllers/BookingController.php:36
 * @route '/getdata'
 */
    const getdataForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: getdata.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BookingController::getdata
 * @see app/Http/Controllers/BookingController.php:36
 * @route '/getdata'
 */
        getdataForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getdata.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BookingController::getdata
 * @see app/Http/Controllers/BookingController.php:36
 * @route '/getdata'
 */
        getdataForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getdata.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    getdata.form = getdataForm
/**
* @see \App\Http\Controllers\BookingController::storeSale
 * @see app/Http/Controllers/BookingController.php:286
 * @route '/booking-sales'
 */
export const storeSale = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeSale.url(options),
    method: 'post',
})

storeSale.definition = {
    methods: ["post"],
    url: '/booking-sales',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\BookingController::storeSale
 * @see app/Http/Controllers/BookingController.php:286
 * @route '/booking-sales'
 */
storeSale.url = (options?: RouteQueryOptions) => {
    return storeSale.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::storeSale
 * @see app/Http/Controllers/BookingController.php:286
 * @route '/booking-sales'
 */
storeSale.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeSale.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\BookingController::storeSale
 * @see app/Http/Controllers/BookingController.php:286
 * @route '/booking-sales'
 */
    const storeSaleForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storeSale.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\BookingController::storeSale
 * @see app/Http/Controllers/BookingController.php:286
 * @route '/booking-sales'
 */
        storeSaleForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storeSale.url(options),
            method: 'post',
        })
    
    storeSale.form = storeSaleForm
/**
* @see \App\Http\Controllers\BookingController::showDetails
 * @see app/Http/Controllers/BookingController.php:589
 * @route '/bookings/{booking}/details'
 */
export const showDetails = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showDetails.url(args, options),
    method: 'get',
})

showDetails.definition = {
    methods: ["get","head"],
    url: '/bookings/{booking}/details',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BookingController::showDetails
 * @see app/Http/Controllers/BookingController.php:589
 * @route '/bookings/{booking}/details'
 */
showDetails.url = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { booking: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { booking: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    booking: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        booking: typeof args.booking === 'object'
                ? args.booking.id
                : args.booking,
                }

    return showDetails.definition.url
            .replace('{booking}', parsedArgs.booking.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::showDetails
 * @see app/Http/Controllers/BookingController.php:589
 * @route '/bookings/{booking}/details'
 */
showDetails.get = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showDetails.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BookingController::showDetails
 * @see app/Http/Controllers/BookingController.php:589
 * @route '/bookings/{booking}/details'
 */
showDetails.head = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: showDetails.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BookingController::showDetails
 * @see app/Http/Controllers/BookingController.php:589
 * @route '/bookings/{booking}/details'
 */
    const showDetailsForm = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: showDetails.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BookingController::showDetails
 * @see app/Http/Controllers/BookingController.php:589
 * @route '/bookings/{booking}/details'
 */
        showDetailsForm.get = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showDetails.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BookingController::showDetails
 * @see app/Http/Controllers/BookingController.php:589
 * @route '/bookings/{booking}/details'
 */
        showDetailsForm.head = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showDetails.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    showDetails.form = showDetailsForm
/**
* @see \App\Http\Controllers\BookingController::markPaymentPending
 * @see app/Http/Controllers/BookingController.php:815
 * @route '/bookings/{booking}/payment-pending'
 */
export const markPaymentPending = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markPaymentPending.url(args, options),
    method: 'post',
})

markPaymentPending.definition = {
    methods: ["post"],
    url: '/bookings/{booking}/payment-pending',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\BookingController::markPaymentPending
 * @see app/Http/Controllers/BookingController.php:815
 * @route '/bookings/{booking}/payment-pending'
 */
markPaymentPending.url = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { booking: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { booking: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    booking: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        booking: typeof args.booking === 'object'
                ? args.booking.id
                : args.booking,
                }

    return markPaymentPending.definition.url
            .replace('{booking}', parsedArgs.booking.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::markPaymentPending
 * @see app/Http/Controllers/BookingController.php:815
 * @route '/bookings/{booking}/payment-pending'
 */
markPaymentPending.post = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markPaymentPending.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\BookingController::markPaymentPending
 * @see app/Http/Controllers/BookingController.php:815
 * @route '/bookings/{booking}/payment-pending'
 */
    const markPaymentPendingForm = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: markPaymentPending.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\BookingController::markPaymentPending
 * @see app/Http/Controllers/BookingController.php:815
 * @route '/bookings/{booking}/payment-pending'
 */
        markPaymentPendingForm.post = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: markPaymentPending.url(args, options),
            method: 'post',
        })
    
    markPaymentPending.form = markPaymentPendingForm
/**
* @see \App\Http\Controllers\BookingController::updateStatus
 * @see app/Http/Controllers/BookingController.php:1462
 * @route '/bookings/{booking}/status'
 */
export const updateStatus = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updateStatus.url(args, options),
    method: 'post',
})

updateStatus.definition = {
    methods: ["post"],
    url: '/bookings/{booking}/status',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\BookingController::updateStatus
 * @see app/Http/Controllers/BookingController.php:1462
 * @route '/bookings/{booking}/status'
 */
updateStatus.url = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { booking: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { booking: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    booking: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        booking: typeof args.booking === 'object'
                ? args.booking.id
                : args.booking,
                }

    return updateStatus.definition.url
            .replace('{booking}', parsedArgs.booking.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::updateStatus
 * @see app/Http/Controllers/BookingController.php:1462
 * @route '/bookings/{booking}/status'
 */
updateStatus.post = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updateStatus.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\BookingController::updateStatus
 * @see app/Http/Controllers/BookingController.php:1462
 * @route '/bookings/{booking}/status'
 */
    const updateStatusForm = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateStatus.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\BookingController::updateStatus
 * @see app/Http/Controllers/BookingController.php:1462
 * @route '/bookings/{booking}/status'
 */
        updateStatusForm.post = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateStatus.url(args, options),
            method: 'post',
        })
    
    updateStatus.form = updateStatusForm
/**
* @see \App\Http\Controllers\BookingController::storeBlockedTime
 * @see app/Http/Controllers/BookingController.php:841
 * @route '/bookings/blocked-time'
 */
export const storeBlockedTime = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeBlockedTime.url(options),
    method: 'post',
})

storeBlockedTime.definition = {
    methods: ["post"],
    url: '/bookings/blocked-time',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\BookingController::storeBlockedTime
 * @see app/Http/Controllers/BookingController.php:841
 * @route '/bookings/blocked-time'
 */
storeBlockedTime.url = (options?: RouteQueryOptions) => {
    return storeBlockedTime.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::storeBlockedTime
 * @see app/Http/Controllers/BookingController.php:841
 * @route '/bookings/blocked-time'
 */
storeBlockedTime.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeBlockedTime.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\BookingController::storeBlockedTime
 * @see app/Http/Controllers/BookingController.php:841
 * @route '/bookings/blocked-time'
 */
    const storeBlockedTimeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storeBlockedTime.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\BookingController::storeBlockedTime
 * @see app/Http/Controllers/BookingController.php:841
 * @route '/bookings/blocked-time'
 */
        storeBlockedTimeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storeBlockedTime.url(options),
            method: 'post',
        })
    
    storeBlockedTime.form = storeBlockedTimeForm
/**
* @see \App\Http\Controllers\BookingController::updateBlockedTime
 * @see app/Http/Controllers/BookingController.php:891
 * @route '/bookings/blocked-time/{booking}'
 */
export const updateBlockedTime = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateBlockedTime.url(args, options),
    method: 'put',
})

updateBlockedTime.definition = {
    methods: ["put"],
    url: '/bookings/blocked-time/{booking}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\BookingController::updateBlockedTime
 * @see app/Http/Controllers/BookingController.php:891
 * @route '/bookings/blocked-time/{booking}'
 */
updateBlockedTime.url = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { booking: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { booking: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    booking: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        booking: typeof args.booking === 'object'
                ? args.booking.id
                : args.booking,
                }

    return updateBlockedTime.definition.url
            .replace('{booking}', parsedArgs.booking.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::updateBlockedTime
 * @see app/Http/Controllers/BookingController.php:891
 * @route '/bookings/blocked-time/{booking}'
 */
updateBlockedTime.put = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateBlockedTime.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\BookingController::updateBlockedTime
 * @see app/Http/Controllers/BookingController.php:891
 * @route '/bookings/blocked-time/{booking}'
 */
    const updateBlockedTimeForm = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateBlockedTime.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\BookingController::updateBlockedTime
 * @see app/Http/Controllers/BookingController.php:891
 * @route '/bookings/blocked-time/{booking}'
 */
        updateBlockedTimeForm.put = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateBlockedTime.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateBlockedTime.form = updateBlockedTimeForm
/**
* @see \App\Http\Controllers\BookingController::emailReceipt
 * @see app/Http/Controllers/BookingController.php:946
 * @route '/bookings/{booking}/email-receipt'
 */
export const emailReceipt = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: emailReceipt.url(args, options),
    method: 'post',
})

emailReceipt.definition = {
    methods: ["post"],
    url: '/bookings/{booking}/email-receipt',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\BookingController::emailReceipt
 * @see app/Http/Controllers/BookingController.php:946
 * @route '/bookings/{booking}/email-receipt'
 */
emailReceipt.url = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { booking: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { booking: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    booking: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        booking: typeof args.booking === 'object'
                ? args.booking.id
                : args.booking,
                }

    return emailReceipt.definition.url
            .replace('{booking}', parsedArgs.booking.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::emailReceipt
 * @see app/Http/Controllers/BookingController.php:946
 * @route '/bookings/{booking}/email-receipt'
 */
emailReceipt.post = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: emailReceipt.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\BookingController::emailReceipt
 * @see app/Http/Controllers/BookingController.php:946
 * @route '/bookings/{booking}/email-receipt'
 */
    const emailReceiptForm = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: emailReceipt.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\BookingController::emailReceipt
 * @see app/Http/Controllers/BookingController.php:946
 * @route '/bookings/{booking}/email-receipt'
 */
        emailReceiptForm.post = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: emailReceipt.url(args, options),
            method: 'post',
        })
    
    emailReceipt.form = emailReceiptForm
/**
* @see \App\Http\Controllers\BookingController::waitlistpending
 * @see app/Http/Controllers/BookingController.php:1055
 * @route '/bookings/pending'
 */
export const waitlistpending = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: waitlistpending.url(options),
    method: 'get',
})

waitlistpending.definition = {
    methods: ["get","head"],
    url: '/bookings/pending',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BookingController::waitlistpending
 * @see app/Http/Controllers/BookingController.php:1055
 * @route '/bookings/pending'
 */
waitlistpending.url = (options?: RouteQueryOptions) => {
    return waitlistpending.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::waitlistpending
 * @see app/Http/Controllers/BookingController.php:1055
 * @route '/bookings/pending'
 */
waitlistpending.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: waitlistpending.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BookingController::waitlistpending
 * @see app/Http/Controllers/BookingController.php:1055
 * @route '/bookings/pending'
 */
waitlistpending.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: waitlistpending.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BookingController::waitlistpending
 * @see app/Http/Controllers/BookingController.php:1055
 * @route '/bookings/pending'
 */
    const waitlistpendingForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: waitlistpending.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BookingController::waitlistpending
 * @see app/Http/Controllers/BookingController.php:1055
 * @route '/bookings/pending'
 */
        waitlistpendingForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: waitlistpending.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BookingController::waitlistpending
 * @see app/Http/Controllers/BookingController.php:1055
 * @route '/bookings/pending'
 */
        waitlistpendingForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: waitlistpending.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    waitlistpending.form = waitlistpendingForm
/**
* @see \App\Http\Controllers\BookingController::waitlistShow
 * @see app/Http/Controllers/BookingController.php:1139
 * @route '/bookings/{booking}/waitlist'
 */
export const waitlistShow = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: waitlistShow.url(args, options),
    method: 'get',
})

waitlistShow.definition = {
    methods: ["get","head"],
    url: '/bookings/{booking}/waitlist',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BookingController::waitlistShow
 * @see app/Http/Controllers/BookingController.php:1139
 * @route '/bookings/{booking}/waitlist'
 */
waitlistShow.url = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { booking: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { booking: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    booking: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        booking: typeof args.booking === 'object'
                ? args.booking.id
                : args.booking,
                }

    return waitlistShow.definition.url
            .replace('{booking}', parsedArgs.booking.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::waitlistShow
 * @see app/Http/Controllers/BookingController.php:1139
 * @route '/bookings/{booking}/waitlist'
 */
waitlistShow.get = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: waitlistShow.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BookingController::waitlistShow
 * @see app/Http/Controllers/BookingController.php:1139
 * @route '/bookings/{booking}/waitlist'
 */
waitlistShow.head = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: waitlistShow.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BookingController::waitlistShow
 * @see app/Http/Controllers/BookingController.php:1139
 * @route '/bookings/{booking}/waitlist'
 */
    const waitlistShowForm = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: waitlistShow.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BookingController::waitlistShow
 * @see app/Http/Controllers/BookingController.php:1139
 * @route '/bookings/{booking}/waitlist'
 */
        waitlistShowForm.get = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: waitlistShow.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BookingController::waitlistShow
 * @see app/Http/Controllers/BookingController.php:1139
 * @route '/bookings/{booking}/waitlist'
 */
        waitlistShowForm.head = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: waitlistShow.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    waitlistShow.form = waitlistShowForm
/**
* @see \App\Http\Controllers\BookingController::waitlistUpdate
 * @see app/Http/Controllers/BookingController.php:1180
 * @route '/bookings/{booking}/waitlist'
 */
export const waitlistUpdate = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: waitlistUpdate.url(args, options),
    method: 'put',
})

waitlistUpdate.definition = {
    methods: ["put"],
    url: '/bookings/{booking}/waitlist',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\BookingController::waitlistUpdate
 * @see app/Http/Controllers/BookingController.php:1180
 * @route '/bookings/{booking}/waitlist'
 */
waitlistUpdate.url = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { booking: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { booking: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    booking: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        booking: typeof args.booking === 'object'
                ? args.booking.id
                : args.booking,
                }

    return waitlistUpdate.definition.url
            .replace('{booking}', parsedArgs.booking.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::waitlistUpdate
 * @see app/Http/Controllers/BookingController.php:1180
 * @route '/bookings/{booking}/waitlist'
 */
waitlistUpdate.put = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: waitlistUpdate.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\BookingController::waitlistUpdate
 * @see app/Http/Controllers/BookingController.php:1180
 * @route '/bookings/{booking}/waitlist'
 */
    const waitlistUpdateForm = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: waitlistUpdate.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\BookingController::waitlistUpdate
 * @see app/Http/Controllers/BookingController.php:1180
 * @route '/bookings/{booking}/waitlist'
 */
        waitlistUpdateForm.put = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: waitlistUpdate.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    waitlistUpdate.form = waitlistUpdateForm
/**
* @see \App\Http\Controllers\BookingController::branchSelect
 * @see app/Http/Controllers/BookingController.php:1293
 * @route '/branches/select'
 */
export const branchSelect = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: branchSelect.url(options),
    method: 'get',
})

branchSelect.definition = {
    methods: ["get","head"],
    url: '/branches/select',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BookingController::branchSelect
 * @see app/Http/Controllers/BookingController.php:1293
 * @route '/branches/select'
 */
branchSelect.url = (options?: RouteQueryOptions) => {
    return branchSelect.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::branchSelect
 * @see app/Http/Controllers/BookingController.php:1293
 * @route '/branches/select'
 */
branchSelect.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: branchSelect.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BookingController::branchSelect
 * @see app/Http/Controllers/BookingController.php:1293
 * @route '/branches/select'
 */
branchSelect.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: branchSelect.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BookingController::branchSelect
 * @see app/Http/Controllers/BookingController.php:1293
 * @route '/branches/select'
 */
    const branchSelectForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: branchSelect.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BookingController::branchSelect
 * @see app/Http/Controllers/BookingController.php:1293
 * @route '/branches/select'
 */
        branchSelectForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: branchSelect.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BookingController::branchSelect
 * @see app/Http/Controllers/BookingController.php:1293
 * @route '/branches/select'
 */
        branchSelectForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: branchSelect.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    branchSelect.form = branchSelectForm
/**
* @see \App\Http\Controllers\BookingController::employeeSelect
 * @see app/Http/Controllers/BookingController.php:1303
 * @route '/employees/select'
 */
export const employeeSelect = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: employeeSelect.url(options),
    method: 'get',
})

employeeSelect.definition = {
    methods: ["get","head"],
    url: '/employees/select',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BookingController::employeeSelect
 * @see app/Http/Controllers/BookingController.php:1303
 * @route '/employees/select'
 */
employeeSelect.url = (options?: RouteQueryOptions) => {
    return employeeSelect.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::employeeSelect
 * @see app/Http/Controllers/BookingController.php:1303
 * @route '/employees/select'
 */
employeeSelect.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: employeeSelect.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BookingController::employeeSelect
 * @see app/Http/Controllers/BookingController.php:1303
 * @route '/employees/select'
 */
employeeSelect.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: employeeSelect.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BookingController::employeeSelect
 * @see app/Http/Controllers/BookingController.php:1303
 * @route '/employees/select'
 */
    const employeeSelectForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: employeeSelect.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BookingController::employeeSelect
 * @see app/Http/Controllers/BookingController.php:1303
 * @route '/employees/select'
 */
        employeeSelectForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: employeeSelect.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BookingController::employeeSelect
 * @see app/Http/Controllers/BookingController.php:1303
 * @route '/employees/select'
 */
        employeeSelectForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: employeeSelect.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    employeeSelect.form = employeeSelectForm
/**
* @see \App\Http\Controllers\BookingController::syncServicesFromTip
 * @see app/Http/Controllers/BookingController.php:470
 * @route '/bookings/{booking}/sync-services'
 */
export const syncServicesFromTip = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: syncServicesFromTip.url(args, options),
    method: 'post',
})

syncServicesFromTip.definition = {
    methods: ["post"],
    url: '/bookings/{booking}/sync-services',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\BookingController::syncServicesFromTip
 * @see app/Http/Controllers/BookingController.php:470
 * @route '/bookings/{booking}/sync-services'
 */
syncServicesFromTip.url = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { booking: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { booking: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    booking: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        booking: typeof args.booking === 'object'
                ? args.booking.id
                : args.booking,
                }

    return syncServicesFromTip.definition.url
            .replace('{booking}', parsedArgs.booking.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::syncServicesFromTip
 * @see app/Http/Controllers/BookingController.php:470
 * @route '/bookings/{booking}/sync-services'
 */
syncServicesFromTip.post = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: syncServicesFromTip.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\BookingController::syncServicesFromTip
 * @see app/Http/Controllers/BookingController.php:470
 * @route '/bookings/{booking}/sync-services'
 */
    const syncServicesFromTipForm = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: syncServicesFromTip.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\BookingController::syncServicesFromTip
 * @see app/Http/Controllers/BookingController.php:470
 * @route '/bookings/{booking}/sync-services'
 */
        syncServicesFromTipForm.post = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: syncServicesFromTip.url(args, options),
            method: 'post',
        })
    
    syncServicesFromTip.form = syncServicesFromTipForm
/**
* @see \App\Http\Controllers\BookingController::updateClient
 * @see app/Http/Controllers/BookingController.php:795
 * @route '/bookings/{booking}/client'
 */
export const updateClient = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updateClient.url(args, options),
    method: 'post',
})

updateClient.definition = {
    methods: ["post"],
    url: '/bookings/{booking}/client',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\BookingController::updateClient
 * @see app/Http/Controllers/BookingController.php:795
 * @route '/bookings/{booking}/client'
 */
updateClient.url = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { booking: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { booking: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    booking: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        booking: typeof args.booking === 'object'
                ? args.booking.id
                : args.booking,
                }

    return updateClient.definition.url
            .replace('{booking}', parsedArgs.booking.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::updateClient
 * @see app/Http/Controllers/BookingController.php:795
 * @route '/bookings/{booking}/client'
 */
updateClient.post = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updateClient.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\BookingController::updateClient
 * @see app/Http/Controllers/BookingController.php:795
 * @route '/bookings/{booking}/client'
 */
    const updateClientForm = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateClient.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\BookingController::updateClient
 * @see app/Http/Controllers/BookingController.php:795
 * @route '/bookings/{booking}/client'
 */
        updateClientForm.post = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateClient.url(args, options),
            method: 'post',
        })
    
    updateClient.form = updateClientForm
/**
* @see \App\Http\Controllers\BookingController::updateNote
 * @see app/Http/Controllers/BookingController.php:1837
 * @route '/bookings/{booking}/note'
 */
export const updateNote = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updateNote.url(args, options),
    method: 'post',
})

updateNote.definition = {
    methods: ["post"],
    url: '/bookings/{booking}/note',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\BookingController::updateNote
 * @see app/Http/Controllers/BookingController.php:1837
 * @route '/bookings/{booking}/note'
 */
updateNote.url = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { booking: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { booking: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    booking: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        booking: typeof args.booking === 'object'
                ? args.booking.id
                : args.booking,
                }

    return updateNote.definition.url
            .replace('{booking}', parsedArgs.booking.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::updateNote
 * @see app/Http/Controllers/BookingController.php:1837
 * @route '/bookings/{booking}/note'
 */
updateNote.post = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updateNote.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\BookingController::updateNote
 * @see app/Http/Controllers/BookingController.php:1837
 * @route '/bookings/{booking}/note'
 */
    const updateNoteForm = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateNote.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\BookingController::updateNote
 * @see app/Http/Controllers/BookingController.php:1837
 * @route '/bookings/{booking}/note'
 */
        updateNoteForm.post = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateNote.url(args, options),
            method: 'post',
        })
    
    updateNote.form = updateNoteForm
/**
* @see \App\Http\Controllers\BookingController::deleteNote
 * @see app/Http/Controllers/BookingController.php:1851
 * @route '/bookings/{booking}/note'
 */
export const deleteNote = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteNote.url(args, options),
    method: 'delete',
})

deleteNote.definition = {
    methods: ["delete"],
    url: '/bookings/{booking}/note',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\BookingController::deleteNote
 * @see app/Http/Controllers/BookingController.php:1851
 * @route '/bookings/{booking}/note'
 */
deleteNote.url = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { booking: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { booking: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    booking: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        booking: typeof args.booking === 'object'
                ? args.booking.id
                : args.booking,
                }

    return deleteNote.definition.url
            .replace('{booking}', parsedArgs.booking.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::deleteNote
 * @see app/Http/Controllers/BookingController.php:1851
 * @route '/bookings/{booking}/note'
 */
deleteNote.delete = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteNote.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\BookingController::deleteNote
 * @see app/Http/Controllers/BookingController.php:1851
 * @route '/bookings/{booking}/note'
 */
    const deleteNoteForm = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: deleteNote.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\BookingController::deleteNote
 * @see app/Http/Controllers/BookingController.php:1851
 * @route '/bookings/{booking}/note'
 */
        deleteNoteForm.delete = (args: { booking: string | number | { id: string | number } } | [booking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: deleteNote.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    deleteNote.form = deleteNoteForm
/**
* @see \App\Http\Controllers\BookingController::editSale
 * @see app/Http/Controllers/BookingController.php:1765
 * @route '/sales/{sale}/edit'
 */
export const editSale = (args: { sale: string | number } | [sale: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: editSale.url(args, options),
    method: 'get',
})

editSale.definition = {
    methods: ["get","head"],
    url: '/sales/{sale}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BookingController::editSale
 * @see app/Http/Controllers/BookingController.php:1765
 * @route '/sales/{sale}/edit'
 */
editSale.url = (args: { sale: string | number } | [sale: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { sale: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    sale: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        sale: args.sale,
                }

    return editSale.definition.url
            .replace('{sale}', parsedArgs.sale.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::editSale
 * @see app/Http/Controllers/BookingController.php:1765
 * @route '/sales/{sale}/edit'
 */
editSale.get = (args: { sale: string | number } | [sale: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: editSale.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BookingController::editSale
 * @see app/Http/Controllers/BookingController.php:1765
 * @route '/sales/{sale}/edit'
 */
editSale.head = (args: { sale: string | number } | [sale: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: editSale.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BookingController::editSale
 * @see app/Http/Controllers/BookingController.php:1765
 * @route '/sales/{sale}/edit'
 */
    const editSaleForm = (args: { sale: string | number } | [sale: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: editSale.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BookingController::editSale
 * @see app/Http/Controllers/BookingController.php:1765
 * @route '/sales/{sale}/edit'
 */
        editSaleForm.get = (args: { sale: string | number } | [sale: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: editSale.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BookingController::editSale
 * @see app/Http/Controllers/BookingController.php:1765
 * @route '/sales/{sale}/edit'
 */
        editSaleForm.head = (args: { sale: string | number } | [sale: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: editSale.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    editSale.form = editSaleForm
/**
* @see \App\Http\Controllers\BookingController::updateSale
 * @see app/Http/Controllers/BookingController.php:1774
 * @route '/sales/{sale}'
 */
export const updateSale = (args: { sale: string | number | { id: string | number } } | [sale: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateSale.url(args, options),
    method: 'patch',
})

updateSale.definition = {
    methods: ["patch"],
    url: '/sales/{sale}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\BookingController::updateSale
 * @see app/Http/Controllers/BookingController.php:1774
 * @route '/sales/{sale}'
 */
updateSale.url = (args: { sale: string | number | { id: string | number } } | [sale: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { sale: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { sale: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    sale: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        sale: typeof args.sale === 'object'
                ? args.sale.id
                : args.sale,
                }

    return updateSale.definition.url
            .replace('{sale}', parsedArgs.sale.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BookingController::updateSale
 * @see app/Http/Controllers/BookingController.php:1774
 * @route '/sales/{sale}'
 */
updateSale.patch = (args: { sale: string | number | { id: string | number } } | [sale: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateSale.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\BookingController::updateSale
 * @see app/Http/Controllers/BookingController.php:1774
 * @route '/sales/{sale}'
 */
    const updateSaleForm = (args: { sale: string | number | { id: string | number } } | [sale: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateSale.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\BookingController::updateSale
 * @see app/Http/Controllers/BookingController.php:1774
 * @route '/sales/{sale}'
 */
        updateSaleForm.patch = (args: { sale: string | number | { id: string | number } } | [sale: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateSale.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateSale.form = updateSaleForm
const BookingController = { store, index, getdata, storeSale, showDetails, markPaymentPending, updateStatus, storeBlockedTime, updateBlockedTime, emailReceipt, waitlistpending, waitlistShow, waitlistUpdate, branchSelect, employeeSelect, syncServicesFromTip, updateClient, updateNote, deleteNote, editSale, updateSale }

export default BookingController

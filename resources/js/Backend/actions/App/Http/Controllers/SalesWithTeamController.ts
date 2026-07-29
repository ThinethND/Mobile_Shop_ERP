import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\SalesWithTeamController::daily
 * @see app/Http/Controllers/SalesWithTeamController.php:197
 * @route '/reports/sales-team/daily'
 */
export const daily = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: daily.url(options),
    method: 'get',
})

daily.definition = {
    methods: ["get","head"],
    url: '/reports/sales-team/daily',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SalesWithTeamController::daily
 * @see app/Http/Controllers/SalesWithTeamController.php:197
 * @route '/reports/sales-team/daily'
 */
daily.url = (options?: RouteQueryOptions) => {
    return daily.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SalesWithTeamController::daily
 * @see app/Http/Controllers/SalesWithTeamController.php:197
 * @route '/reports/sales-team/daily'
 */
daily.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: daily.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\SalesWithTeamController::daily
 * @see app/Http/Controllers/SalesWithTeamController.php:197
 * @route '/reports/sales-team/daily'
 */
daily.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: daily.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\SalesWithTeamController::daily
 * @see app/Http/Controllers/SalesWithTeamController.php:197
 * @route '/reports/sales-team/daily'
 */
    const dailyForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: daily.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\SalesWithTeamController::daily
 * @see app/Http/Controllers/SalesWithTeamController.php:197
 * @route '/reports/sales-team/daily'
 */
        dailyForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: daily.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\SalesWithTeamController::daily
 * @see app/Http/Controllers/SalesWithTeamController.php:197
 * @route '/reports/sales-team/daily'
 */
        dailyForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: daily.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    daily.form = dailyForm
/**
* @see \App\Http\Controllers\SalesWithTeamController::dailyPdf
 * @see app/Http/Controllers/SalesWithTeamController.php:203
 * @route '/reports/sales-team/daily/pdf'
 */
export const dailyPdf = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dailyPdf.url(options),
    method: 'get',
})

dailyPdf.definition = {
    methods: ["get","head"],
    url: '/reports/sales-team/daily/pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SalesWithTeamController::dailyPdf
 * @see app/Http/Controllers/SalesWithTeamController.php:203
 * @route '/reports/sales-team/daily/pdf'
 */
dailyPdf.url = (options?: RouteQueryOptions) => {
    return dailyPdf.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SalesWithTeamController::dailyPdf
 * @see app/Http/Controllers/SalesWithTeamController.php:203
 * @route '/reports/sales-team/daily/pdf'
 */
dailyPdf.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dailyPdf.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\SalesWithTeamController::dailyPdf
 * @see app/Http/Controllers/SalesWithTeamController.php:203
 * @route '/reports/sales-team/daily/pdf'
 */
dailyPdf.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dailyPdf.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\SalesWithTeamController::dailyPdf
 * @see app/Http/Controllers/SalesWithTeamController.php:203
 * @route '/reports/sales-team/daily/pdf'
 */
    const dailyPdfForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dailyPdf.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\SalesWithTeamController::dailyPdf
 * @see app/Http/Controllers/SalesWithTeamController.php:203
 * @route '/reports/sales-team/daily/pdf'
 */
        dailyPdfForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dailyPdf.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\SalesWithTeamController::dailyPdf
 * @see app/Http/Controllers/SalesWithTeamController.php:203
 * @route '/reports/sales-team/daily/pdf'
 */
        dailyPdfForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dailyPdf.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    dailyPdf.form = dailyPdfForm
/**
* @see \App\Http\Controllers\SalesWithTeamController::dailyExcel
 * @see app/Http/Controllers/SalesWithTeamController.php:214
 * @route '/reports/sales-team/daily/excel'
 */
export const dailyExcel = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dailyExcel.url(options),
    method: 'get',
})

dailyExcel.definition = {
    methods: ["get","head"],
    url: '/reports/sales-team/daily/excel',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SalesWithTeamController::dailyExcel
 * @see app/Http/Controllers/SalesWithTeamController.php:214
 * @route '/reports/sales-team/daily/excel'
 */
dailyExcel.url = (options?: RouteQueryOptions) => {
    return dailyExcel.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SalesWithTeamController::dailyExcel
 * @see app/Http/Controllers/SalesWithTeamController.php:214
 * @route '/reports/sales-team/daily/excel'
 */
dailyExcel.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dailyExcel.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\SalesWithTeamController::dailyExcel
 * @see app/Http/Controllers/SalesWithTeamController.php:214
 * @route '/reports/sales-team/daily/excel'
 */
dailyExcel.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dailyExcel.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\SalesWithTeamController::dailyExcel
 * @see app/Http/Controllers/SalesWithTeamController.php:214
 * @route '/reports/sales-team/daily/excel'
 */
    const dailyExcelForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dailyExcel.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\SalesWithTeamController::dailyExcel
 * @see app/Http/Controllers/SalesWithTeamController.php:214
 * @route '/reports/sales-team/daily/excel'
 */
        dailyExcelForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dailyExcel.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\SalesWithTeamController::dailyExcel
 * @see app/Http/Controllers/SalesWithTeamController.php:214
 * @route '/reports/sales-team/daily/excel'
 */
        dailyExcelForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dailyExcel.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    dailyExcel.form = dailyExcelForm
/**
* @see \App\Http\Controllers\SalesWithTeamController::monthly
 * @see app/Http/Controllers/SalesWithTeamController.php:326
 * @route '/reports/sales-team/monthly'
 */
export const monthly = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: monthly.url(options),
    method: 'get',
})

monthly.definition = {
    methods: ["get","head"],
    url: '/reports/sales-team/monthly',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SalesWithTeamController::monthly
 * @see app/Http/Controllers/SalesWithTeamController.php:326
 * @route '/reports/sales-team/monthly'
 */
monthly.url = (options?: RouteQueryOptions) => {
    return monthly.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SalesWithTeamController::monthly
 * @see app/Http/Controllers/SalesWithTeamController.php:326
 * @route '/reports/sales-team/monthly'
 */
monthly.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: monthly.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\SalesWithTeamController::monthly
 * @see app/Http/Controllers/SalesWithTeamController.php:326
 * @route '/reports/sales-team/monthly'
 */
monthly.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: monthly.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\SalesWithTeamController::monthly
 * @see app/Http/Controllers/SalesWithTeamController.php:326
 * @route '/reports/sales-team/monthly'
 */
    const monthlyForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: monthly.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\SalesWithTeamController::monthly
 * @see app/Http/Controllers/SalesWithTeamController.php:326
 * @route '/reports/sales-team/monthly'
 */
        monthlyForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: monthly.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\SalesWithTeamController::monthly
 * @see app/Http/Controllers/SalesWithTeamController.php:326
 * @route '/reports/sales-team/monthly'
 */
        monthlyForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: monthly.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    monthly.form = monthlyForm
/**
* @see \App\Http\Controllers\SalesWithTeamController::monthlyPdf
 * @see app/Http/Controllers/SalesWithTeamController.php:332
 * @route '/reports/sales-team/monthly/pdf'
 */
export const monthlyPdf = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: monthlyPdf.url(options),
    method: 'get',
})

monthlyPdf.definition = {
    methods: ["get","head"],
    url: '/reports/sales-team/monthly/pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SalesWithTeamController::monthlyPdf
 * @see app/Http/Controllers/SalesWithTeamController.php:332
 * @route '/reports/sales-team/monthly/pdf'
 */
monthlyPdf.url = (options?: RouteQueryOptions) => {
    return monthlyPdf.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SalesWithTeamController::monthlyPdf
 * @see app/Http/Controllers/SalesWithTeamController.php:332
 * @route '/reports/sales-team/monthly/pdf'
 */
monthlyPdf.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: monthlyPdf.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\SalesWithTeamController::monthlyPdf
 * @see app/Http/Controllers/SalesWithTeamController.php:332
 * @route '/reports/sales-team/monthly/pdf'
 */
monthlyPdf.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: monthlyPdf.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\SalesWithTeamController::monthlyPdf
 * @see app/Http/Controllers/SalesWithTeamController.php:332
 * @route '/reports/sales-team/monthly/pdf'
 */
    const monthlyPdfForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: monthlyPdf.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\SalesWithTeamController::monthlyPdf
 * @see app/Http/Controllers/SalesWithTeamController.php:332
 * @route '/reports/sales-team/monthly/pdf'
 */
        monthlyPdfForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: monthlyPdf.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\SalesWithTeamController::monthlyPdf
 * @see app/Http/Controllers/SalesWithTeamController.php:332
 * @route '/reports/sales-team/monthly/pdf'
 */
        monthlyPdfForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: monthlyPdf.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    monthlyPdf.form = monthlyPdfForm
/**
* @see \App\Http\Controllers\SalesWithTeamController::monthlyExcel
 * @see app/Http/Controllers/SalesWithTeamController.php:346
 * @route '/reports/sales-team/monthly/excel'
 */
export const monthlyExcel = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: monthlyExcel.url(options),
    method: 'get',
})

monthlyExcel.definition = {
    methods: ["get","head"],
    url: '/reports/sales-team/monthly/excel',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SalesWithTeamController::monthlyExcel
 * @see app/Http/Controllers/SalesWithTeamController.php:346
 * @route '/reports/sales-team/monthly/excel'
 */
monthlyExcel.url = (options?: RouteQueryOptions) => {
    return monthlyExcel.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SalesWithTeamController::monthlyExcel
 * @see app/Http/Controllers/SalesWithTeamController.php:346
 * @route '/reports/sales-team/monthly/excel'
 */
monthlyExcel.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: monthlyExcel.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\SalesWithTeamController::monthlyExcel
 * @see app/Http/Controllers/SalesWithTeamController.php:346
 * @route '/reports/sales-team/monthly/excel'
 */
monthlyExcel.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: monthlyExcel.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\SalesWithTeamController::monthlyExcel
 * @see app/Http/Controllers/SalesWithTeamController.php:346
 * @route '/reports/sales-team/monthly/excel'
 */
    const monthlyExcelForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: monthlyExcel.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\SalesWithTeamController::monthlyExcel
 * @see app/Http/Controllers/SalesWithTeamController.php:346
 * @route '/reports/sales-team/monthly/excel'
 */
        monthlyExcelForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: monthlyExcel.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\SalesWithTeamController::monthlyExcel
 * @see app/Http/Controllers/SalesWithTeamController.php:346
 * @route '/reports/sales-team/monthly/excel'
 */
        monthlyExcelForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: monthlyExcel.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    monthlyExcel.form = monthlyExcelForm
const SalesWithTeamController = { daily, dailyPdf, dailyExcel, monthly, monthlyPdf, monthlyExcel }

export default SalesWithTeamController

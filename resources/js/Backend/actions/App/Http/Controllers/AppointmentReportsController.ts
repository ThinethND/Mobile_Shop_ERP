import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\AppointmentReportsController::summary
 * @see app/Http/Controllers/AppointmentReportsController.php:214
 * @route '/appointments/summary'
 */
export const summary = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: summary.url(options),
    method: 'get',
})

summary.definition = {
    methods: ["get","head"],
    url: '/appointments/summary',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AppointmentReportsController::summary
 * @see app/Http/Controllers/AppointmentReportsController.php:214
 * @route '/appointments/summary'
 */
summary.url = (options?: RouteQueryOptions) => {
    return summary.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AppointmentReportsController::summary
 * @see app/Http/Controllers/AppointmentReportsController.php:214
 * @route '/appointments/summary'
 */
summary.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: summary.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AppointmentReportsController::summary
 * @see app/Http/Controllers/AppointmentReportsController.php:214
 * @route '/appointments/summary'
 */
summary.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: summary.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AppointmentReportsController::summary
 * @see app/Http/Controllers/AppointmentReportsController.php:214
 * @route '/appointments/summary'
 */
    const summaryForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: summary.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AppointmentReportsController::summary
 * @see app/Http/Controllers/AppointmentReportsController.php:214
 * @route '/appointments/summary'
 */
        summaryForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: summary.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AppointmentReportsController::summary
 * @see app/Http/Controllers/AppointmentReportsController.php:214
 * @route '/appointments/summary'
 */
        summaryForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: summary.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    summary.form = summaryForm
/**
* @see \App\Http\Controllers\AppointmentReportsController::summaryPdf
 * @see app/Http/Controllers/AppointmentReportsController.php:220
 * @route '/appointments/summary/pdf'
 */
export const summaryPdf = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: summaryPdf.url(options),
    method: 'get',
})

summaryPdf.definition = {
    methods: ["get","head"],
    url: '/appointments/summary/pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AppointmentReportsController::summaryPdf
 * @see app/Http/Controllers/AppointmentReportsController.php:220
 * @route '/appointments/summary/pdf'
 */
summaryPdf.url = (options?: RouteQueryOptions) => {
    return summaryPdf.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AppointmentReportsController::summaryPdf
 * @see app/Http/Controllers/AppointmentReportsController.php:220
 * @route '/appointments/summary/pdf'
 */
summaryPdf.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: summaryPdf.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AppointmentReportsController::summaryPdf
 * @see app/Http/Controllers/AppointmentReportsController.php:220
 * @route '/appointments/summary/pdf'
 */
summaryPdf.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: summaryPdf.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AppointmentReportsController::summaryPdf
 * @see app/Http/Controllers/AppointmentReportsController.php:220
 * @route '/appointments/summary/pdf'
 */
    const summaryPdfForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: summaryPdf.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AppointmentReportsController::summaryPdf
 * @see app/Http/Controllers/AppointmentReportsController.php:220
 * @route '/appointments/summary/pdf'
 */
        summaryPdfForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: summaryPdf.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AppointmentReportsController::summaryPdf
 * @see app/Http/Controllers/AppointmentReportsController.php:220
 * @route '/appointments/summary/pdf'
 */
        summaryPdfForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: summaryPdf.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    summaryPdf.form = summaryPdfForm
/**
* @see \App\Http\Controllers\AppointmentReportsController::summaryExcel
 * @see app/Http/Controllers/AppointmentReportsController.php:231
 * @route '/appointments/summary/excel'
 */
export const summaryExcel = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: summaryExcel.url(options),
    method: 'get',
})

summaryExcel.definition = {
    methods: ["get","head"],
    url: '/appointments/summary/excel',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AppointmentReportsController::summaryExcel
 * @see app/Http/Controllers/AppointmentReportsController.php:231
 * @route '/appointments/summary/excel'
 */
summaryExcel.url = (options?: RouteQueryOptions) => {
    return summaryExcel.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AppointmentReportsController::summaryExcel
 * @see app/Http/Controllers/AppointmentReportsController.php:231
 * @route '/appointments/summary/excel'
 */
summaryExcel.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: summaryExcel.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AppointmentReportsController::summaryExcel
 * @see app/Http/Controllers/AppointmentReportsController.php:231
 * @route '/appointments/summary/excel'
 */
summaryExcel.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: summaryExcel.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AppointmentReportsController::summaryExcel
 * @see app/Http/Controllers/AppointmentReportsController.php:231
 * @route '/appointments/summary/excel'
 */
    const summaryExcelForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: summaryExcel.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AppointmentReportsController::summaryExcel
 * @see app/Http/Controllers/AppointmentReportsController.php:231
 * @route '/appointments/summary/excel'
 */
        summaryExcelForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: summaryExcel.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AppointmentReportsController::summaryExcel
 * @see app/Http/Controllers/AppointmentReportsController.php:231
 * @route '/appointments/summary/excel'
 */
        summaryExcelForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: summaryExcel.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    summaryExcel.form = summaryExcelForm
/**
* @see \App\Http\Controllers\AppointmentReportsController::list
 * @see app/Http/Controllers/AppointmentReportsController.php:249
 * @route '/appointments/list'
 */
export const list = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: list.url(options),
    method: 'get',
})

list.definition = {
    methods: ["get","head"],
    url: '/appointments/list',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AppointmentReportsController::list
 * @see app/Http/Controllers/AppointmentReportsController.php:249
 * @route '/appointments/list'
 */
list.url = (options?: RouteQueryOptions) => {
    return list.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AppointmentReportsController::list
 * @see app/Http/Controllers/AppointmentReportsController.php:249
 * @route '/appointments/list'
 */
list.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: list.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AppointmentReportsController::list
 * @see app/Http/Controllers/AppointmentReportsController.php:249
 * @route '/appointments/list'
 */
list.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: list.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AppointmentReportsController::list
 * @see app/Http/Controllers/AppointmentReportsController.php:249
 * @route '/appointments/list'
 */
    const listForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: list.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AppointmentReportsController::list
 * @see app/Http/Controllers/AppointmentReportsController.php:249
 * @route '/appointments/list'
 */
        listForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: list.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AppointmentReportsController::list
 * @see app/Http/Controllers/AppointmentReportsController.php:249
 * @route '/appointments/list'
 */
        listForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: list.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    list.form = listForm
/**
* @see \App\Http\Controllers\AppointmentReportsController::listPdf
 * @see app/Http/Controllers/AppointmentReportsController.php:255
 * @route '/appointments/list/pdf'
 */
export const listPdf = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: listPdf.url(options),
    method: 'get',
})

listPdf.definition = {
    methods: ["get","head"],
    url: '/appointments/list/pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AppointmentReportsController::listPdf
 * @see app/Http/Controllers/AppointmentReportsController.php:255
 * @route '/appointments/list/pdf'
 */
listPdf.url = (options?: RouteQueryOptions) => {
    return listPdf.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AppointmentReportsController::listPdf
 * @see app/Http/Controllers/AppointmentReportsController.php:255
 * @route '/appointments/list/pdf'
 */
listPdf.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: listPdf.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AppointmentReportsController::listPdf
 * @see app/Http/Controllers/AppointmentReportsController.php:255
 * @route '/appointments/list/pdf'
 */
listPdf.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: listPdf.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AppointmentReportsController::listPdf
 * @see app/Http/Controllers/AppointmentReportsController.php:255
 * @route '/appointments/list/pdf'
 */
    const listPdfForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: listPdf.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AppointmentReportsController::listPdf
 * @see app/Http/Controllers/AppointmentReportsController.php:255
 * @route '/appointments/list/pdf'
 */
        listPdfForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: listPdf.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AppointmentReportsController::listPdf
 * @see app/Http/Controllers/AppointmentReportsController.php:255
 * @route '/appointments/list/pdf'
 */
        listPdfForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: listPdf.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    listPdf.form = listPdfForm
/**
* @see \App\Http\Controllers\AppointmentReportsController::listExcel
 * @see app/Http/Controllers/AppointmentReportsController.php:267
 * @route '/appointments/list/excel'
 */
export const listExcel = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: listExcel.url(options),
    method: 'get',
})

listExcel.definition = {
    methods: ["get","head"],
    url: '/appointments/list/excel',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AppointmentReportsController::listExcel
 * @see app/Http/Controllers/AppointmentReportsController.php:267
 * @route '/appointments/list/excel'
 */
listExcel.url = (options?: RouteQueryOptions) => {
    return listExcel.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AppointmentReportsController::listExcel
 * @see app/Http/Controllers/AppointmentReportsController.php:267
 * @route '/appointments/list/excel'
 */
listExcel.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: listExcel.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\AppointmentReportsController::listExcel
 * @see app/Http/Controllers/AppointmentReportsController.php:267
 * @route '/appointments/list/excel'
 */
listExcel.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: listExcel.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\AppointmentReportsController::listExcel
 * @see app/Http/Controllers/AppointmentReportsController.php:267
 * @route '/appointments/list/excel'
 */
    const listExcelForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: listExcel.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\AppointmentReportsController::listExcel
 * @see app/Http/Controllers/AppointmentReportsController.php:267
 * @route '/appointments/list/excel'
 */
        listExcelForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: listExcel.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\AppointmentReportsController::listExcel
 * @see app/Http/Controllers/AppointmentReportsController.php:267
 * @route '/appointments/list/excel'
 */
        listExcelForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: listExcel.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    listExcel.form = listExcelForm
const AppointmentReportsController = { summary, summaryPdf, summaryExcel, list, listPdf, listExcel }

export default AppointmentReportsController

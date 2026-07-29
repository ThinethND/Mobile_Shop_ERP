import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\ClientReportController::list
 * @see app/Http/Controllers/ClientReportController.php:114
 * @route '/reports/clients/list'
 */
export const list = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: list.url(options),
    method: 'get',
})

list.definition = {
    methods: ["get","head"],
    url: '/reports/clients/list',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ClientReportController::list
 * @see app/Http/Controllers/ClientReportController.php:114
 * @route '/reports/clients/list'
 */
list.url = (options?: RouteQueryOptions) => {
    return list.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ClientReportController::list
 * @see app/Http/Controllers/ClientReportController.php:114
 * @route '/reports/clients/list'
 */
list.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: list.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ClientReportController::list
 * @see app/Http/Controllers/ClientReportController.php:114
 * @route '/reports/clients/list'
 */
list.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: list.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\ClientReportController::list
 * @see app/Http/Controllers/ClientReportController.php:114
 * @route '/reports/clients/list'
 */
    const listForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: list.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\ClientReportController::list
 * @see app/Http/Controllers/ClientReportController.php:114
 * @route '/reports/clients/list'
 */
        listForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: list.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\ClientReportController::list
 * @see app/Http/Controllers/ClientReportController.php:114
 * @route '/reports/clients/list'
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
* @see \App\Http\Controllers\ClientReportController::listPdf
 * @see app/Http/Controllers/ClientReportController.php:120
 * @route '/reports/clients/list/pdf'
 */
export const listPdf = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: listPdf.url(options),
    method: 'get',
})

listPdf.definition = {
    methods: ["get","head"],
    url: '/reports/clients/list/pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ClientReportController::listPdf
 * @see app/Http/Controllers/ClientReportController.php:120
 * @route '/reports/clients/list/pdf'
 */
listPdf.url = (options?: RouteQueryOptions) => {
    return listPdf.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ClientReportController::listPdf
 * @see app/Http/Controllers/ClientReportController.php:120
 * @route '/reports/clients/list/pdf'
 */
listPdf.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: listPdf.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ClientReportController::listPdf
 * @see app/Http/Controllers/ClientReportController.php:120
 * @route '/reports/clients/list/pdf'
 */
listPdf.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: listPdf.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\ClientReportController::listPdf
 * @see app/Http/Controllers/ClientReportController.php:120
 * @route '/reports/clients/list/pdf'
 */
    const listPdfForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: listPdf.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\ClientReportController::listPdf
 * @see app/Http/Controllers/ClientReportController.php:120
 * @route '/reports/clients/list/pdf'
 */
        listPdfForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: listPdf.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\ClientReportController::listPdf
 * @see app/Http/Controllers/ClientReportController.php:120
 * @route '/reports/clients/list/pdf'
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
* @see \App\Http\Controllers\ClientReportController::listExcel
 * @see app/Http/Controllers/ClientReportController.php:131
 * @route '/reports/clients/list/excel'
 */
export const listExcel = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: listExcel.url(options),
    method: 'get',
})

listExcel.definition = {
    methods: ["get","head"],
    url: '/reports/clients/list/excel',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ClientReportController::listExcel
 * @see app/Http/Controllers/ClientReportController.php:131
 * @route '/reports/clients/list/excel'
 */
listExcel.url = (options?: RouteQueryOptions) => {
    return listExcel.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ClientReportController::listExcel
 * @see app/Http/Controllers/ClientReportController.php:131
 * @route '/reports/clients/list/excel'
 */
listExcel.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: listExcel.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ClientReportController::listExcel
 * @see app/Http/Controllers/ClientReportController.php:131
 * @route '/reports/clients/list/excel'
 */
listExcel.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: listExcel.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\ClientReportController::listExcel
 * @see app/Http/Controllers/ClientReportController.php:131
 * @route '/reports/clients/list/excel'
 */
    const listExcelForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: listExcel.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\ClientReportController::listExcel
 * @see app/Http/Controllers/ClientReportController.php:131
 * @route '/reports/clients/list/excel'
 */
        listExcelForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: listExcel.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\ClientReportController::listExcel
 * @see app/Http/Controllers/ClientReportController.php:131
 * @route '/reports/clients/list/excel'
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
const ClientReportController = { list, listPdf, listExcel }

export default ClientReportController

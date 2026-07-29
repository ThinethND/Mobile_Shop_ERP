import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\PublicInvoiceController::show
 * @see app/Http/Controllers/PublicInvoiceController.php:12
 * @route '/invoice/{sale}'
 */
export const show = (args: { sale: string | number | { id: string | number } } | [sale: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/invoice/{sale}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PublicInvoiceController::show
 * @see app/Http/Controllers/PublicInvoiceController.php:12
 * @route '/invoice/{sale}'
 */
show.url = (args: { sale: string | number | { id: string | number } } | [sale: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return show.definition.url
            .replace('{sale}', parsedArgs.sale.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PublicInvoiceController::show
 * @see app/Http/Controllers/PublicInvoiceController.php:12
 * @route '/invoice/{sale}'
 */
show.get = (args: { sale: string | number | { id: string | number } } | [sale: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PublicInvoiceController::show
 * @see app/Http/Controllers/PublicInvoiceController.php:12
 * @route '/invoice/{sale}'
 */
show.head = (args: { sale: string | number | { id: string | number } } | [sale: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PublicInvoiceController::show
 * @see app/Http/Controllers/PublicInvoiceController.php:12
 * @route '/invoice/{sale}'
 */
    const showForm = (args: { sale: string | number | { id: string | number } } | [sale: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PublicInvoiceController::show
 * @see app/Http/Controllers/PublicInvoiceController.php:12
 * @route '/invoice/{sale}'
 */
        showForm.get = (args: { sale: string | number | { id: string | number } } | [sale: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PublicInvoiceController::show
 * @see app/Http/Controllers/PublicInvoiceController.php:12
 * @route '/invoice/{sale}'
 */
        showForm.head = (args: { sale: string | number | { id: string | number } } | [sale: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \App\Http\Controllers\PublicInvoiceController::pdf
 * @see app/Http/Controllers/PublicInvoiceController.php:50
 * @route '/invoice/{sale}/pdf'
 */
export const pdf = (args: { sale: string | number | { id: string | number } } | [sale: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pdf.url(args, options),
    method: 'get',
})

pdf.definition = {
    methods: ["get","head"],
    url: '/invoice/{sale}/pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PublicInvoiceController::pdf
 * @see app/Http/Controllers/PublicInvoiceController.php:50
 * @route '/invoice/{sale}/pdf'
 */
pdf.url = (args: { sale: string | number | { id: string | number } } | [sale: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return pdf.definition.url
            .replace('{sale}', parsedArgs.sale.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PublicInvoiceController::pdf
 * @see app/Http/Controllers/PublicInvoiceController.php:50
 * @route '/invoice/{sale}/pdf'
 */
pdf.get = (args: { sale: string | number | { id: string | number } } | [sale: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pdf.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PublicInvoiceController::pdf
 * @see app/Http/Controllers/PublicInvoiceController.php:50
 * @route '/invoice/{sale}/pdf'
 */
pdf.head = (args: { sale: string | number | { id: string | number } } | [sale: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: pdf.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\PublicInvoiceController::pdf
 * @see app/Http/Controllers/PublicInvoiceController.php:50
 * @route '/invoice/{sale}/pdf'
 */
    const pdfForm = (args: { sale: string | number | { id: string | number } } | [sale: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: pdf.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\PublicInvoiceController::pdf
 * @see app/Http/Controllers/PublicInvoiceController.php:50
 * @route '/invoice/{sale}/pdf'
 */
        pdfForm.get = (args: { sale: string | number | { id: string | number } } | [sale: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: pdf.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\PublicInvoiceController::pdf
 * @see app/Http/Controllers/PublicInvoiceController.php:50
 * @route '/invoice/{sale}/pdf'
 */
        pdfForm.head = (args: { sale: string | number | { id: string | number } } | [sale: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: pdf.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    pdf.form = pdfForm
const PublicInvoiceController = { show, pdf }

export default PublicInvoiceController

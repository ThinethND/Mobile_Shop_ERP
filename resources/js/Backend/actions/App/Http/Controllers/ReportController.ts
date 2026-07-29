import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\ReportController::index
 * @see app/Http/Controllers/ReportController.php:19
 * @route '/reports'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/reports',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ReportController::index
 * @see app/Http/Controllers/ReportController.php:19
 * @route '/reports'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ReportController::index
 * @see app/Http/Controllers/ReportController.php:19
 * @route '/reports'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ReportController::index
 * @see app/Http/Controllers/ReportController.php:19
 * @route '/reports'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\ReportController::index
 * @see app/Http/Controllers/ReportController.php:19
 * @route '/reports'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\ReportController::index
 * @see app/Http/Controllers/ReportController.php:19
 * @route '/reports'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\ReportController::index
 * @see app/Http/Controllers/ReportController.php:19
 * @route '/reports'
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
* @see \App\Http\Controllers\ReportController::getdata
 * @see app/Http/Controllers/ReportController.php:24
 * @route '/reports/getdata'
 */
export const getdata = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getdata.url(options),
    method: 'get',
})

getdata.definition = {
    methods: ["get","head"],
    url: '/reports/getdata',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ReportController::getdata
 * @see app/Http/Controllers/ReportController.php:24
 * @route '/reports/getdata'
 */
getdata.url = (options?: RouteQueryOptions) => {
    return getdata.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ReportController::getdata
 * @see app/Http/Controllers/ReportController.php:24
 * @route '/reports/getdata'
 */
getdata.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getdata.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ReportController::getdata
 * @see app/Http/Controllers/ReportController.php:24
 * @route '/reports/getdata'
 */
getdata.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getdata.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\ReportController::getdata
 * @see app/Http/Controllers/ReportController.php:24
 * @route '/reports/getdata'
 */
    const getdataForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: getdata.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\ReportController::getdata
 * @see app/Http/Controllers/ReportController.php:24
 * @route '/reports/getdata'
 */
        getdataForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getdata.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\ReportController::getdata
 * @see app/Http/Controllers/ReportController.php:24
 * @route '/reports/getdata'
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
* @see \App\Http\Controllers\ReportController::create
 * @see app/Http/Controllers/ReportController.php:29
 * @route '/reports/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/reports/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ReportController::create
 * @see app/Http/Controllers/ReportController.php:29
 * @route '/reports/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ReportController::create
 * @see app/Http/Controllers/ReportController.php:29
 * @route '/reports/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ReportController::create
 * @see app/Http/Controllers/ReportController.php:29
 * @route '/reports/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\ReportController::create
 * @see app/Http/Controllers/ReportController.php:29
 * @route '/reports/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\ReportController::create
 * @see app/Http/Controllers/ReportController.php:29
 * @route '/reports/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\ReportController::create
 * @see app/Http/Controllers/ReportController.php:29
 * @route '/reports/create'
 */
        createForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    create.form = createForm
/**
* @see \App\Http\Controllers\ReportController::store
 * @see app/Http/Controllers/ReportController.php:34
 * @route '/reports'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/reports',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\ReportController::store
 * @see app/Http/Controllers/ReportController.php:34
 * @route '/reports'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ReportController::store
 * @see app/Http/Controllers/ReportController.php:34
 * @route '/reports'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\ReportController::store
 * @see app/Http/Controllers/ReportController.php:34
 * @route '/reports'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\ReportController::store
 * @see app/Http/Controllers/ReportController.php:34
 * @route '/reports'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\ReportController::edit
 * @see app/Http/Controllers/ReportController.php:39
 * @route '/reports/{id}/edit'
 */
export const edit = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/reports/{id}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ReportController::edit
 * @see app/Http/Controllers/ReportController.php:39
 * @route '/reports/{id}/edit'
 */
edit.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return edit.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ReportController::edit
 * @see app/Http/Controllers/ReportController.php:39
 * @route '/reports/{id}/edit'
 */
edit.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ReportController::edit
 * @see app/Http/Controllers/ReportController.php:39
 * @route '/reports/{id}/edit'
 */
edit.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\ReportController::edit
 * @see app/Http/Controllers/ReportController.php:39
 * @route '/reports/{id}/edit'
 */
    const editForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\ReportController::edit
 * @see app/Http/Controllers/ReportController.php:39
 * @route '/reports/{id}/edit'
 */
        editForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\ReportController::edit
 * @see app/Http/Controllers/ReportController.php:39
 * @route '/reports/{id}/edit'
 */
        editForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    edit.form = editForm
/**
* @see \App\Http\Controllers\ReportController::update
 * @see app/Http/Controllers/ReportController.php:46
 * @route '/reports/update'
 */
export const update = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

update.definition = {
    methods: ["post"],
    url: '/reports/update',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\ReportController::update
 * @see app/Http/Controllers/ReportController.php:46
 * @route '/reports/update'
 */
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ReportController::update
 * @see app/Http/Controllers/ReportController.php:46
 * @route '/reports/update'
 */
update.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\ReportController::update
 * @see app/Http/Controllers/ReportController.php:46
 * @route '/reports/update'
 */
    const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\ReportController::update
 * @see app/Http/Controllers/ReportController.php:46
 * @route '/reports/update'
 */
        updateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(options),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\ReportController::destroy
 * @see app/Http/Controllers/ReportController.php:51
 * @route '/reports/{id}'
 */
export const destroy = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/reports/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\ReportController::destroy
 * @see app/Http/Controllers/ReportController.php:51
 * @route '/reports/{id}'
 */
destroy.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return destroy.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ReportController::destroy
 * @see app/Http/Controllers/ReportController.php:51
 * @route '/reports/{id}'
 */
destroy.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\ReportController::destroy
 * @see app/Http/Controllers/ReportController.php:51
 * @route '/reports/{id}'
 */
    const destroyForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\ReportController::destroy
 * @see app/Http/Controllers/ReportController.php:51
 * @route '/reports/{id}'
 */
        destroyForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
/**
* @see \App\Http\Controllers\ReportController::revenue
 * @see app/Http/Controllers/ReportController.php:59
 * @route '/reports/revenue'
 */
export const revenue = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: revenue.url(options),
    method: 'get',
})

revenue.definition = {
    methods: ["get","head"],
    url: '/reports/revenue',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ReportController::revenue
 * @see app/Http/Controllers/ReportController.php:59
 * @route '/reports/revenue'
 */
revenue.url = (options?: RouteQueryOptions) => {
    return revenue.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ReportController::revenue
 * @see app/Http/Controllers/ReportController.php:59
 * @route '/reports/revenue'
 */
revenue.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: revenue.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ReportController::revenue
 * @see app/Http/Controllers/ReportController.php:59
 * @route '/reports/revenue'
 */
revenue.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: revenue.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\ReportController::revenue
 * @see app/Http/Controllers/ReportController.php:59
 * @route '/reports/revenue'
 */
    const revenueForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: revenue.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\ReportController::revenue
 * @see app/Http/Controllers/ReportController.php:59
 * @route '/reports/revenue'
 */
        revenueForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: revenue.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\ReportController::revenue
 * @see app/Http/Controllers/ReportController.php:59
 * @route '/reports/revenue'
 */
        revenueForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: revenue.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    revenue.form = revenueForm
/**
* @see \App\Http\Controllers\ReportController::revenuePdf
 * @see app/Http/Controllers/ReportController.php:121
 * @route '/reports/revenue/pdf'
 */
export const revenuePdf = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: revenuePdf.url(options),
    method: 'get',
})

revenuePdf.definition = {
    methods: ["get","head"],
    url: '/reports/revenue/pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ReportController::revenuePdf
 * @see app/Http/Controllers/ReportController.php:121
 * @route '/reports/revenue/pdf'
 */
revenuePdf.url = (options?: RouteQueryOptions) => {
    return revenuePdf.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ReportController::revenuePdf
 * @see app/Http/Controllers/ReportController.php:121
 * @route '/reports/revenue/pdf'
 */
revenuePdf.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: revenuePdf.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ReportController::revenuePdf
 * @see app/Http/Controllers/ReportController.php:121
 * @route '/reports/revenue/pdf'
 */
revenuePdf.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: revenuePdf.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\ReportController::revenuePdf
 * @see app/Http/Controllers/ReportController.php:121
 * @route '/reports/revenue/pdf'
 */
    const revenuePdfForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: revenuePdf.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\ReportController::revenuePdf
 * @see app/Http/Controllers/ReportController.php:121
 * @route '/reports/revenue/pdf'
 */
        revenuePdfForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: revenuePdf.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\ReportController::revenuePdf
 * @see app/Http/Controllers/ReportController.php:121
 * @route '/reports/revenue/pdf'
 */
        revenuePdfForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: revenuePdf.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    revenuePdf.form = revenuePdfForm
/**
* @see \App\Http\Controllers\ReportController::revenueExcel
 * @see app/Http/Controllers/ReportController.php:191
 * @route '/reports/reports/revenue/excel'
 */
export const revenueExcel = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: revenueExcel.url(options),
    method: 'get',
})

revenueExcel.definition = {
    methods: ["get","head"],
    url: '/reports/reports/revenue/excel',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ReportController::revenueExcel
 * @see app/Http/Controllers/ReportController.php:191
 * @route '/reports/reports/revenue/excel'
 */
revenueExcel.url = (options?: RouteQueryOptions) => {
    return revenueExcel.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ReportController::revenueExcel
 * @see app/Http/Controllers/ReportController.php:191
 * @route '/reports/reports/revenue/excel'
 */
revenueExcel.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: revenueExcel.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ReportController::revenueExcel
 * @see app/Http/Controllers/ReportController.php:191
 * @route '/reports/reports/revenue/excel'
 */
revenueExcel.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: revenueExcel.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\ReportController::revenueExcel
 * @see app/Http/Controllers/ReportController.php:191
 * @route '/reports/reports/revenue/excel'
 */
    const revenueExcelForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: revenueExcel.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\ReportController::revenueExcel
 * @see app/Http/Controllers/ReportController.php:191
 * @route '/reports/reports/revenue/excel'
 */
        revenueExcelForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: revenueExcel.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\ReportController::revenueExcel
 * @see app/Http/Controllers/ReportController.php:191
 * @route '/reports/reports/revenue/excel'
 */
        revenueExcelForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: revenueExcel.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    revenueExcel.form = revenueExcelForm
/**
* @see \App\Http\Controllers\ReportController::dailySales
 * @see app/Http/Controllers/ReportController.php:208
 * @route '/reports/daily-sales'
 */
export const dailySales = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dailySales.url(options),
    method: 'get',
})

dailySales.definition = {
    methods: ["get","head"],
    url: '/reports/daily-sales',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ReportController::dailySales
 * @see app/Http/Controllers/ReportController.php:208
 * @route '/reports/daily-sales'
 */
dailySales.url = (options?: RouteQueryOptions) => {
    return dailySales.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ReportController::dailySales
 * @see app/Http/Controllers/ReportController.php:208
 * @route '/reports/daily-sales'
 */
dailySales.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dailySales.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ReportController::dailySales
 * @see app/Http/Controllers/ReportController.php:208
 * @route '/reports/daily-sales'
 */
dailySales.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dailySales.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\ReportController::dailySales
 * @see app/Http/Controllers/ReportController.php:208
 * @route '/reports/daily-sales'
 */
    const dailySalesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dailySales.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\ReportController::dailySales
 * @see app/Http/Controllers/ReportController.php:208
 * @route '/reports/daily-sales'
 */
        dailySalesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dailySales.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\ReportController::dailySales
 * @see app/Http/Controllers/ReportController.php:208
 * @route '/reports/daily-sales'
 */
        dailySalesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dailySales.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    dailySales.form = dailySalesForm
/**
* @see \App\Http\Controllers\ReportController::dailySalesPdf
 * @see app/Http/Controllers/ReportController.php:228
 * @route '/reports/daily-sales/pdf'
 */
export const dailySalesPdf = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dailySalesPdf.url(options),
    method: 'get',
})

dailySalesPdf.definition = {
    methods: ["get","head"],
    url: '/reports/daily-sales/pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ReportController::dailySalesPdf
 * @see app/Http/Controllers/ReportController.php:228
 * @route '/reports/daily-sales/pdf'
 */
dailySalesPdf.url = (options?: RouteQueryOptions) => {
    return dailySalesPdf.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ReportController::dailySalesPdf
 * @see app/Http/Controllers/ReportController.php:228
 * @route '/reports/daily-sales/pdf'
 */
dailySalesPdf.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dailySalesPdf.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ReportController::dailySalesPdf
 * @see app/Http/Controllers/ReportController.php:228
 * @route '/reports/daily-sales/pdf'
 */
dailySalesPdf.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dailySalesPdf.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\ReportController::dailySalesPdf
 * @see app/Http/Controllers/ReportController.php:228
 * @route '/reports/daily-sales/pdf'
 */
    const dailySalesPdfForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dailySalesPdf.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\ReportController::dailySalesPdf
 * @see app/Http/Controllers/ReportController.php:228
 * @route '/reports/daily-sales/pdf'
 */
        dailySalesPdfForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dailySalesPdf.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\ReportController::dailySalesPdf
 * @see app/Http/Controllers/ReportController.php:228
 * @route '/reports/daily-sales/pdf'
 */
        dailySalesPdfForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dailySalesPdf.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    dailySalesPdf.form = dailySalesPdfForm
/**
* @see \App\Http\Controllers\ReportController::dailySalesExcel
 * @see app/Http/Controllers/ReportController.php:252
 * @route '/reports/daily-sales/excel'
 */
export const dailySalesExcel = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dailySalesExcel.url(options),
    method: 'get',
})

dailySalesExcel.definition = {
    methods: ["get","head"],
    url: '/reports/daily-sales/excel',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ReportController::dailySalesExcel
 * @see app/Http/Controllers/ReportController.php:252
 * @route '/reports/daily-sales/excel'
 */
dailySalesExcel.url = (options?: RouteQueryOptions) => {
    return dailySalesExcel.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ReportController::dailySalesExcel
 * @see app/Http/Controllers/ReportController.php:252
 * @route '/reports/daily-sales/excel'
 */
dailySalesExcel.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dailySalesExcel.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ReportController::dailySalesExcel
 * @see app/Http/Controllers/ReportController.php:252
 * @route '/reports/daily-sales/excel'
 */
dailySalesExcel.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dailySalesExcel.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\ReportController::dailySalesExcel
 * @see app/Http/Controllers/ReportController.php:252
 * @route '/reports/daily-sales/excel'
 */
    const dailySalesExcelForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dailySalesExcel.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\ReportController::dailySalesExcel
 * @see app/Http/Controllers/ReportController.php:252
 * @route '/reports/daily-sales/excel'
 */
        dailySalesExcelForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dailySalesExcel.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\ReportController::dailySalesExcel
 * @see app/Http/Controllers/ReportController.php:252
 * @route '/reports/daily-sales/excel'
 */
        dailySalesExcelForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dailySalesExcel.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    dailySalesExcel.form = dailySalesExcelForm
const ReportController = { index, getdata, create, store, edit, update, destroy, revenue, revenuePdf, revenueExcel, dailySales, dailySalesPdf, dailySalesExcel }

export default ReportController

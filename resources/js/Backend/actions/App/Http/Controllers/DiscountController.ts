import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\DiscountController::index
 * @see app/Http/Controllers/DiscountController.php:26
 * @route '/discounts'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/discounts',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DiscountController::index
 * @see app/Http/Controllers/DiscountController.php:26
 * @route '/discounts'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DiscountController::index
 * @see app/Http/Controllers/DiscountController.php:26
 * @route '/discounts'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DiscountController::index
 * @see app/Http/Controllers/DiscountController.php:26
 * @route '/discounts'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DiscountController::index
 * @see app/Http/Controllers/DiscountController.php:26
 * @route '/discounts'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DiscountController::index
 * @see app/Http/Controllers/DiscountController.php:26
 * @route '/discounts'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DiscountController::index
 * @see app/Http/Controllers/DiscountController.php:26
 * @route '/discounts'
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
* @see \App\Http\Controllers\DiscountController::getdata
 * @see app/Http/Controllers/DiscountController.php:31
 * @route '/discounts/getdata'
 */
export const getdata = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getdata.url(options),
    method: 'get',
})

getdata.definition = {
    methods: ["get","head"],
    url: '/discounts/getdata',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DiscountController::getdata
 * @see app/Http/Controllers/DiscountController.php:31
 * @route '/discounts/getdata'
 */
getdata.url = (options?: RouteQueryOptions) => {
    return getdata.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DiscountController::getdata
 * @see app/Http/Controllers/DiscountController.php:31
 * @route '/discounts/getdata'
 */
getdata.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getdata.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DiscountController::getdata
 * @see app/Http/Controllers/DiscountController.php:31
 * @route '/discounts/getdata'
 */
getdata.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getdata.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DiscountController::getdata
 * @see app/Http/Controllers/DiscountController.php:31
 * @route '/discounts/getdata'
 */
    const getdataForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: getdata.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DiscountController::getdata
 * @see app/Http/Controllers/DiscountController.php:31
 * @route '/discounts/getdata'
 */
        getdataForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getdata.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DiscountController::getdata
 * @see app/Http/Controllers/DiscountController.php:31
 * @route '/discounts/getdata'
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
* @see \App\Http\Controllers\DiscountController::create
 * @see app/Http/Controllers/DiscountController.php:90
 * @route '/discounts/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/discounts/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DiscountController::create
 * @see app/Http/Controllers/DiscountController.php:90
 * @route '/discounts/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DiscountController::create
 * @see app/Http/Controllers/DiscountController.php:90
 * @route '/discounts/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DiscountController::create
 * @see app/Http/Controllers/DiscountController.php:90
 * @route '/discounts/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DiscountController::create
 * @see app/Http/Controllers/DiscountController.php:90
 * @route '/discounts/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DiscountController::create
 * @see app/Http/Controllers/DiscountController.php:90
 * @route '/discounts/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DiscountController::create
 * @see app/Http/Controllers/DiscountController.php:90
 * @route '/discounts/create'
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
* @see \App\Http\Controllers\DiscountController::store
 * @see app/Http/Controllers/DiscountController.php:101
 * @route '/discounts/store'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/discounts/store',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\DiscountController::store
 * @see app/Http/Controllers/DiscountController.php:101
 * @route '/discounts/store'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DiscountController::store
 * @see app/Http/Controllers/DiscountController.php:101
 * @route '/discounts/store'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\DiscountController::store
 * @see app/Http/Controllers/DiscountController.php:101
 * @route '/discounts/store'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\DiscountController::store
 * @see app/Http/Controllers/DiscountController.php:101
 * @route '/discounts/store'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\DiscountController::edit
 * @see app/Http/Controllers/DiscountController.php:168
 * @route '/discounts/edit/{id}'
 */
export const edit = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/discounts/edit/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DiscountController::edit
 * @see app/Http/Controllers/DiscountController.php:168
 * @route '/discounts/edit/{id}'
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
* @see \App\Http\Controllers\DiscountController::edit
 * @see app/Http/Controllers/DiscountController.php:168
 * @route '/discounts/edit/{id}'
 */
edit.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DiscountController::edit
 * @see app/Http/Controllers/DiscountController.php:168
 * @route '/discounts/edit/{id}'
 */
edit.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DiscountController::edit
 * @see app/Http/Controllers/DiscountController.php:168
 * @route '/discounts/edit/{id}'
 */
    const editForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DiscountController::edit
 * @see app/Http/Controllers/DiscountController.php:168
 * @route '/discounts/edit/{id}'
 */
        editForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DiscountController::edit
 * @see app/Http/Controllers/DiscountController.php:168
 * @route '/discounts/edit/{id}'
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
* @see \App\Http\Controllers\DiscountController::update
 * @see app/Http/Controllers/DiscountController.php:181
 * @route '/discounts/update'
 */
export const update = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

update.definition = {
    methods: ["post"],
    url: '/discounts/update',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\DiscountController::update
 * @see app/Http/Controllers/DiscountController.php:181
 * @route '/discounts/update'
 */
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DiscountController::update
 * @see app/Http/Controllers/DiscountController.php:181
 * @route '/discounts/update'
 */
update.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\DiscountController::update
 * @see app/Http/Controllers/DiscountController.php:181
 * @route '/discounts/update'
 */
    const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\DiscountController::update
 * @see app/Http/Controllers/DiscountController.php:181
 * @route '/discounts/update'
 */
        updateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(options),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\DiscountController::destroy
 * @see app/Http/Controllers/DiscountController.php:261
 * @route '/discounts/{id}'
 */
export const destroy = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/discounts/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\DiscountController::destroy
 * @see app/Http/Controllers/DiscountController.php:261
 * @route '/discounts/{id}'
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
* @see \App\Http\Controllers\DiscountController::destroy
 * @see app/Http/Controllers/DiscountController.php:261
 * @route '/discounts/{id}'
 */
destroy.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\DiscountController::destroy
 * @see app/Http/Controllers/DiscountController.php:261
 * @route '/discounts/{id}'
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
* @see \App\Http\Controllers\DiscountController::destroy
 * @see app/Http/Controllers/DiscountController.php:261
 * @route '/discounts/{id}'
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
* @see \App\Http\Controllers\DiscountController::toggleStatus
 * @see app/Http/Controllers/DiscountController.php:239
 * @route '/discounts/{id}/toggle-status'
 */
export const toggleStatus = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggleStatus.url(args, options),
    method: 'post',
})

toggleStatus.definition = {
    methods: ["post"],
    url: '/discounts/{id}/toggle-status',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\DiscountController::toggleStatus
 * @see app/Http/Controllers/DiscountController.php:239
 * @route '/discounts/{id}/toggle-status'
 */
toggleStatus.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return toggleStatus.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DiscountController::toggleStatus
 * @see app/Http/Controllers/DiscountController.php:239
 * @route '/discounts/{id}/toggle-status'
 */
toggleStatus.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggleStatus.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\DiscountController::toggleStatus
 * @see app/Http/Controllers/DiscountController.php:239
 * @route '/discounts/{id}/toggle-status'
 */
    const toggleStatusForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggleStatus.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\DiscountController::toggleStatus
 * @see app/Http/Controllers/DiscountController.php:239
 * @route '/discounts/{id}/toggle-status'
 */
        toggleStatusForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: toggleStatus.url(args, options),
            method: 'post',
        })
    
    toggleStatus.form = toggleStatusForm
/**
* @see \App\Http\Controllers\DiscountController::select
 * @see app/Http/Controllers/DiscountController.php:281
 * @route '/discounts/select'
 */
export const select = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: select.url(options),
    method: 'get',
})

select.definition = {
    methods: ["get","head"],
    url: '/discounts/select',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DiscountController::select
 * @see app/Http/Controllers/DiscountController.php:281
 * @route '/discounts/select'
 */
select.url = (options?: RouteQueryOptions) => {
    return select.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DiscountController::select
 * @see app/Http/Controllers/DiscountController.php:281
 * @route '/discounts/select'
 */
select.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: select.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DiscountController::select
 * @see app/Http/Controllers/DiscountController.php:281
 * @route '/discounts/select'
 */
select.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: select.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DiscountController::select
 * @see app/Http/Controllers/DiscountController.php:281
 * @route '/discounts/select'
 */
    const selectForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: select.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DiscountController::select
 * @see app/Http/Controllers/DiscountController.php:281
 * @route '/discounts/select'
 */
        selectForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: select.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DiscountController::select
 * @see app/Http/Controllers/DiscountController.php:281
 * @route '/discounts/select'
 */
        selectForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: select.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    select.form = selectForm
const DiscountController = { index, getdata, create, store, edit, update, destroy, toggleStatus, select }

export default DiscountController

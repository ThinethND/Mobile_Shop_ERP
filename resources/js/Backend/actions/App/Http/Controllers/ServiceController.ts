import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\ServiceController::index
 * @see app/Http/Controllers/ServiceController.php:22
 * @route '/service'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/service',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ServiceController::index
 * @see app/Http/Controllers/ServiceController.php:22
 * @route '/service'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ServiceController::index
 * @see app/Http/Controllers/ServiceController.php:22
 * @route '/service'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ServiceController::index
 * @see app/Http/Controllers/ServiceController.php:22
 * @route '/service'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\ServiceController::index
 * @see app/Http/Controllers/ServiceController.php:22
 * @route '/service'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\ServiceController::index
 * @see app/Http/Controllers/ServiceController.php:22
 * @route '/service'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\ServiceController::index
 * @see app/Http/Controllers/ServiceController.php:22
 * @route '/service'
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
* @see \App\Http\Controllers\ServiceController::getdata
 * @see app/Http/Controllers/ServiceController.php:68
 * @route '/service/getdata'
 */
export const getdata = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getdata.url(options),
    method: 'get',
})

getdata.definition = {
    methods: ["get","head"],
    url: '/service/getdata',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ServiceController::getdata
 * @see app/Http/Controllers/ServiceController.php:68
 * @route '/service/getdata'
 */
getdata.url = (options?: RouteQueryOptions) => {
    return getdata.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ServiceController::getdata
 * @see app/Http/Controllers/ServiceController.php:68
 * @route '/service/getdata'
 */
getdata.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getdata.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ServiceController::getdata
 * @see app/Http/Controllers/ServiceController.php:68
 * @route '/service/getdata'
 */
getdata.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getdata.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\ServiceController::getdata
 * @see app/Http/Controllers/ServiceController.php:68
 * @route '/service/getdata'
 */
    const getdataForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: getdata.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\ServiceController::getdata
 * @see app/Http/Controllers/ServiceController.php:68
 * @route '/service/getdata'
 */
        getdataForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getdata.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\ServiceController::getdata
 * @see app/Http/Controllers/ServiceController.php:68
 * @route '/service/getdata'
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
* @see \App\Http\Controllers\ServiceController::create
 * @see app/Http/Controllers/ServiceController.php:144
 * @route '/service/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/service/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ServiceController::create
 * @see app/Http/Controllers/ServiceController.php:144
 * @route '/service/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ServiceController::create
 * @see app/Http/Controllers/ServiceController.php:144
 * @route '/service/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ServiceController::create
 * @see app/Http/Controllers/ServiceController.php:144
 * @route '/service/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\ServiceController::create
 * @see app/Http/Controllers/ServiceController.php:144
 * @route '/service/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\ServiceController::create
 * @see app/Http/Controllers/ServiceController.php:144
 * @route '/service/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\ServiceController::create
 * @see app/Http/Controllers/ServiceController.php:144
 * @route '/service/create'
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
* @see \App\Http\Controllers\ServiceController::store
 * @see app/Http/Controllers/ServiceController.php:158
 * @route '/service/store'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/service/store',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\ServiceController::store
 * @see app/Http/Controllers/ServiceController.php:158
 * @route '/service/store'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ServiceController::store
 * @see app/Http/Controllers/ServiceController.php:158
 * @route '/service/store'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\ServiceController::store
 * @see app/Http/Controllers/ServiceController.php:158
 * @route '/service/store'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\ServiceController::store
 * @see app/Http/Controllers/ServiceController.php:158
 * @route '/service/store'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\ServiceController::edit
 * @see app/Http/Controllers/ServiceController.php:202
 * @route '/service/edit/{id}'
 */
export const edit = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/service/edit/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ServiceController::edit
 * @see app/Http/Controllers/ServiceController.php:202
 * @route '/service/edit/{id}'
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
* @see \App\Http\Controllers\ServiceController::edit
 * @see app/Http/Controllers/ServiceController.php:202
 * @route '/service/edit/{id}'
 */
edit.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ServiceController::edit
 * @see app/Http/Controllers/ServiceController.php:202
 * @route '/service/edit/{id}'
 */
edit.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\ServiceController::edit
 * @see app/Http/Controllers/ServiceController.php:202
 * @route '/service/edit/{id}'
 */
    const editForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\ServiceController::edit
 * @see app/Http/Controllers/ServiceController.php:202
 * @route '/service/edit/{id}'
 */
        editForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\ServiceController::edit
 * @see app/Http/Controllers/ServiceController.php:202
 * @route '/service/edit/{id}'
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
* @see \App\Http\Controllers\ServiceController::update
 * @see app/Http/Controllers/ServiceController.php:219
 * @route '/service/update'
 */
export const update = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

update.definition = {
    methods: ["post"],
    url: '/service/update',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\ServiceController::update
 * @see app/Http/Controllers/ServiceController.php:219
 * @route '/service/update'
 */
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ServiceController::update
 * @see app/Http/Controllers/ServiceController.php:219
 * @route '/service/update'
 */
update.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\ServiceController::update
 * @see app/Http/Controllers/ServiceController.php:219
 * @route '/service/update'
 */
    const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\ServiceController::update
 * @see app/Http/Controllers/ServiceController.php:219
 * @route '/service/update'
 */
        updateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(options),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\ServiceController::destroy
 * @see app/Http/Controllers/ServiceController.php:252
 * @route '/service/delete'
 */
export const destroy = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: destroy.url(options),
    method: 'post',
})

destroy.definition = {
    methods: ["post"],
    url: '/service/delete',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\ServiceController::destroy
 * @see app/Http/Controllers/ServiceController.php:252
 * @route '/service/delete'
 */
destroy.url = (options?: RouteQueryOptions) => {
    return destroy.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ServiceController::destroy
 * @see app/Http/Controllers/ServiceController.php:252
 * @route '/service/delete'
 */
destroy.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: destroy.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\ServiceController::destroy
 * @see app/Http/Controllers/ServiceController.php:252
 * @route '/service/delete'
 */
    const destroyForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\ServiceController::destroy
 * @see app/Http/Controllers/ServiceController.php:252
 * @route '/service/delete'
 */
        destroyForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(options),
            method: 'post',
        })
    
    destroy.form = destroyForm
/**
* @see \App\Http\Controllers\ServiceController::updateStatus
 * @see app/Http/Controllers/ServiceController.php:275
 * @route '/service/status/update'
 */
export const updateStatus = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateStatus.url(options),
    method: 'put',
})

updateStatus.definition = {
    methods: ["put"],
    url: '/service/status/update',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\ServiceController::updateStatus
 * @see app/Http/Controllers/ServiceController.php:275
 * @route '/service/status/update'
 */
updateStatus.url = (options?: RouteQueryOptions) => {
    return updateStatus.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ServiceController::updateStatus
 * @see app/Http/Controllers/ServiceController.php:275
 * @route '/service/status/update'
 */
updateStatus.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateStatus.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\ServiceController::updateStatus
 * @see app/Http/Controllers/ServiceController.php:275
 * @route '/service/status/update'
 */
    const updateStatusForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateStatus.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\ServiceController::updateStatus
 * @see app/Http/Controllers/ServiceController.php:275
 * @route '/service/status/update'
 */
        updateStatusForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateStatus.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateStatus.form = updateStatusForm
/**
* @see \App\Http\Controllers\ServiceController::categoryGetdata
 * @see app/Http/Controllers/ServiceController.php:295
 * @route '/service/categories/getdata'
 */
export const categoryGetdata = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: categoryGetdata.url(options),
    method: 'get',
})

categoryGetdata.definition = {
    methods: ["get","head"],
    url: '/service/categories/getdata',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ServiceController::categoryGetdata
 * @see app/Http/Controllers/ServiceController.php:295
 * @route '/service/categories/getdata'
 */
categoryGetdata.url = (options?: RouteQueryOptions) => {
    return categoryGetdata.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ServiceController::categoryGetdata
 * @see app/Http/Controllers/ServiceController.php:295
 * @route '/service/categories/getdata'
 */
categoryGetdata.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: categoryGetdata.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ServiceController::categoryGetdata
 * @see app/Http/Controllers/ServiceController.php:295
 * @route '/service/categories/getdata'
 */
categoryGetdata.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: categoryGetdata.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\ServiceController::categoryGetdata
 * @see app/Http/Controllers/ServiceController.php:295
 * @route '/service/categories/getdata'
 */
    const categoryGetdataForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: categoryGetdata.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\ServiceController::categoryGetdata
 * @see app/Http/Controllers/ServiceController.php:295
 * @route '/service/categories/getdata'
 */
        categoryGetdataForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: categoryGetdata.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\ServiceController::categoryGetdata
 * @see app/Http/Controllers/ServiceController.php:295
 * @route '/service/categories/getdata'
 */
        categoryGetdataForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: categoryGetdata.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    categoryGetdata.form = categoryGetdataForm
/**
* @see \App\Http\Controllers\ServiceController::categoryStatusUpdate
 * @see app/Http/Controllers/ServiceController.php:458
 * @route '/service/category/status/update'
 */
export const categoryStatusUpdate = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: categoryStatusUpdate.url(options),
    method: 'put',
})

categoryStatusUpdate.definition = {
    methods: ["put"],
    url: '/service/category/status/update',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\ServiceController::categoryStatusUpdate
 * @see app/Http/Controllers/ServiceController.php:458
 * @route '/service/category/status/update'
 */
categoryStatusUpdate.url = (options?: RouteQueryOptions) => {
    return categoryStatusUpdate.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ServiceController::categoryStatusUpdate
 * @see app/Http/Controllers/ServiceController.php:458
 * @route '/service/category/status/update'
 */
categoryStatusUpdate.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: categoryStatusUpdate.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\ServiceController::categoryStatusUpdate
 * @see app/Http/Controllers/ServiceController.php:458
 * @route '/service/category/status/update'
 */
    const categoryStatusUpdateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: categoryStatusUpdate.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\ServiceController::categoryStatusUpdate
 * @see app/Http/Controllers/ServiceController.php:458
 * @route '/service/category/status/update'
 */
        categoryStatusUpdateForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: categoryStatusUpdate.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    categoryStatusUpdate.form = categoryStatusUpdateForm
/**
* @see \App\Http\Controllers\ServiceController::categoryStore
 * @see app/Http/Controllers/ServiceController.php:350
 * @route '/service/category/store'
 */
export const categoryStore = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: categoryStore.url(options),
    method: 'post',
})

categoryStore.definition = {
    methods: ["post"],
    url: '/service/category/store',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\ServiceController::categoryStore
 * @see app/Http/Controllers/ServiceController.php:350
 * @route '/service/category/store'
 */
categoryStore.url = (options?: RouteQueryOptions) => {
    return categoryStore.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ServiceController::categoryStore
 * @see app/Http/Controllers/ServiceController.php:350
 * @route '/service/category/store'
 */
categoryStore.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: categoryStore.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\ServiceController::categoryStore
 * @see app/Http/Controllers/ServiceController.php:350
 * @route '/service/category/store'
 */
    const categoryStoreForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: categoryStore.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\ServiceController::categoryStore
 * @see app/Http/Controllers/ServiceController.php:350
 * @route '/service/category/store'
 */
        categoryStoreForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: categoryStore.url(options),
            method: 'post',
        })
    
    categoryStore.form = categoryStoreForm
/**
* @see \App\Http\Controllers\ServiceController::categoryUpdate
 * @see app/Http/Controllers/ServiceController.php:382
 * @route '/service/category/update'
 */
export const categoryUpdate = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: categoryUpdate.url(options),
    method: 'post',
})

categoryUpdate.definition = {
    methods: ["post"],
    url: '/service/category/update',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\ServiceController::categoryUpdate
 * @see app/Http/Controllers/ServiceController.php:382
 * @route '/service/category/update'
 */
categoryUpdate.url = (options?: RouteQueryOptions) => {
    return categoryUpdate.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ServiceController::categoryUpdate
 * @see app/Http/Controllers/ServiceController.php:382
 * @route '/service/category/update'
 */
categoryUpdate.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: categoryUpdate.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\ServiceController::categoryUpdate
 * @see app/Http/Controllers/ServiceController.php:382
 * @route '/service/category/update'
 */
    const categoryUpdateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: categoryUpdate.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\ServiceController::categoryUpdate
 * @see app/Http/Controllers/ServiceController.php:382
 * @route '/service/category/update'
 */
        categoryUpdateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: categoryUpdate.url(options),
            method: 'post',
        })
    
    categoryUpdate.form = categoryUpdateForm
/**
* @see \App\Http\Controllers\ServiceController::categoryDestroy
 * @see app/Http/Controllers/ServiceController.php:418
 * @route '/service/categories/destroy'
 */
export const categoryDestroy = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: categoryDestroy.url(options),
    method: 'post',
})

categoryDestroy.definition = {
    methods: ["post"],
    url: '/service/categories/destroy',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\ServiceController::categoryDestroy
 * @see app/Http/Controllers/ServiceController.php:418
 * @route '/service/categories/destroy'
 */
categoryDestroy.url = (options?: RouteQueryOptions) => {
    return categoryDestroy.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ServiceController::categoryDestroy
 * @see app/Http/Controllers/ServiceController.php:418
 * @route '/service/categories/destroy'
 */
categoryDestroy.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: categoryDestroy.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\ServiceController::categoryDestroy
 * @see app/Http/Controllers/ServiceController.php:418
 * @route '/service/categories/destroy'
 */
    const categoryDestroyForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: categoryDestroy.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\ServiceController::categoryDestroy
 * @see app/Http/Controllers/ServiceController.php:418
 * @route '/service/categories/destroy'
 */
        categoryDestroyForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: categoryDestroy.url(options),
            method: 'post',
        })
    
    categoryDestroy.form = categoryDestroyForm
/**
* @see \App\Http\Controllers\ServiceController::variantGetdata
 * @see app/Http/Controllers/ServiceController.php:478
 * @route '/service/{serviceId}/variants/getdata'
 */
export const variantGetdata = (args: { serviceId: string | number } | [serviceId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: variantGetdata.url(args, options),
    method: 'get',
})

variantGetdata.definition = {
    methods: ["get","head"],
    url: '/service/{serviceId}/variants/getdata',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ServiceController::variantGetdata
 * @see app/Http/Controllers/ServiceController.php:478
 * @route '/service/{serviceId}/variants/getdata'
 */
variantGetdata.url = (args: { serviceId: string | number } | [serviceId: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { serviceId: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    serviceId: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        serviceId: args.serviceId,
                }

    return variantGetdata.definition.url
            .replace('{serviceId}', parsedArgs.serviceId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ServiceController::variantGetdata
 * @see app/Http/Controllers/ServiceController.php:478
 * @route '/service/{serviceId}/variants/getdata'
 */
variantGetdata.get = (args: { serviceId: string | number } | [serviceId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: variantGetdata.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ServiceController::variantGetdata
 * @see app/Http/Controllers/ServiceController.php:478
 * @route '/service/{serviceId}/variants/getdata'
 */
variantGetdata.head = (args: { serviceId: string | number } | [serviceId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: variantGetdata.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\ServiceController::variantGetdata
 * @see app/Http/Controllers/ServiceController.php:478
 * @route '/service/{serviceId}/variants/getdata'
 */
    const variantGetdataForm = (args: { serviceId: string | number } | [serviceId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: variantGetdata.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\ServiceController::variantGetdata
 * @see app/Http/Controllers/ServiceController.php:478
 * @route '/service/{serviceId}/variants/getdata'
 */
        variantGetdataForm.get = (args: { serviceId: string | number } | [serviceId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: variantGetdata.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\ServiceController::variantGetdata
 * @see app/Http/Controllers/ServiceController.php:478
 * @route '/service/{serviceId}/variants/getdata'
 */
        variantGetdataForm.head = (args: { serviceId: string | number } | [serviceId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: variantGetdata.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    variantGetdata.form = variantGetdataForm
/**
* @see \App\Http\Controllers\ServiceController::variantStore
 * @see app/Http/Controllers/ServiceController.php:525
 * @route '/service/variant/store'
 */
export const variantStore = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: variantStore.url(options),
    method: 'post',
})

variantStore.definition = {
    methods: ["post"],
    url: '/service/variant/store',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\ServiceController::variantStore
 * @see app/Http/Controllers/ServiceController.php:525
 * @route '/service/variant/store'
 */
variantStore.url = (options?: RouteQueryOptions) => {
    return variantStore.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ServiceController::variantStore
 * @see app/Http/Controllers/ServiceController.php:525
 * @route '/service/variant/store'
 */
variantStore.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: variantStore.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\ServiceController::variantStore
 * @see app/Http/Controllers/ServiceController.php:525
 * @route '/service/variant/store'
 */
    const variantStoreForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: variantStore.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\ServiceController::variantStore
 * @see app/Http/Controllers/ServiceController.php:525
 * @route '/service/variant/store'
 */
        variantStoreForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: variantStore.url(options),
            method: 'post',
        })
    
    variantStore.form = variantStoreForm
/**
* @see \App\Http\Controllers\ServiceController::variantUpdate
 * @see app/Http/Controllers/ServiceController.php:559
 * @route '/service/variant/update'
 */
export const variantUpdate = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: variantUpdate.url(options),
    method: 'post',
})

variantUpdate.definition = {
    methods: ["post"],
    url: '/service/variant/update',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\ServiceController::variantUpdate
 * @see app/Http/Controllers/ServiceController.php:559
 * @route '/service/variant/update'
 */
variantUpdate.url = (options?: RouteQueryOptions) => {
    return variantUpdate.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ServiceController::variantUpdate
 * @see app/Http/Controllers/ServiceController.php:559
 * @route '/service/variant/update'
 */
variantUpdate.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: variantUpdate.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\ServiceController::variantUpdate
 * @see app/Http/Controllers/ServiceController.php:559
 * @route '/service/variant/update'
 */
    const variantUpdateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: variantUpdate.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\ServiceController::variantUpdate
 * @see app/Http/Controllers/ServiceController.php:559
 * @route '/service/variant/update'
 */
        variantUpdateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: variantUpdate.url(options),
            method: 'post',
        })
    
    variantUpdate.form = variantUpdateForm
/**
* @see \App\Http\Controllers\ServiceController::variantDestroy
 * @see app/Http/Controllers/ServiceController.php:593
 * @route '/service/variant/delete'
 */
export const variantDestroy = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: variantDestroy.url(options),
    method: 'post',
})

variantDestroy.definition = {
    methods: ["post"],
    url: '/service/variant/delete',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\ServiceController::variantDestroy
 * @see app/Http/Controllers/ServiceController.php:593
 * @route '/service/variant/delete'
 */
variantDestroy.url = (options?: RouteQueryOptions) => {
    return variantDestroy.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ServiceController::variantDestroy
 * @see app/Http/Controllers/ServiceController.php:593
 * @route '/service/variant/delete'
 */
variantDestroy.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: variantDestroy.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\ServiceController::variantDestroy
 * @see app/Http/Controllers/ServiceController.php:593
 * @route '/service/variant/delete'
 */
    const variantDestroyForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: variantDestroy.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\ServiceController::variantDestroy
 * @see app/Http/Controllers/ServiceController.php:593
 * @route '/service/variant/delete'
 */
        variantDestroyForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: variantDestroy.url(options),
            method: 'post',
        })
    
    variantDestroy.form = variantDestroyForm
/**
* @see \App\Http\Controllers\ServiceController::reorder
 * @see app/Http/Controllers/ServiceController.php:635
 * @route '/service/reorder'
 */
export const reorder = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reorder.url(options),
    method: 'post',
})

reorder.definition = {
    methods: ["post"],
    url: '/service/reorder',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\ServiceController::reorder
 * @see app/Http/Controllers/ServiceController.php:635
 * @route '/service/reorder'
 */
reorder.url = (options?: RouteQueryOptions) => {
    return reorder.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ServiceController::reorder
 * @see app/Http/Controllers/ServiceController.php:635
 * @route '/service/reorder'
 */
reorder.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reorder.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\ServiceController::reorder
 * @see app/Http/Controllers/ServiceController.php:635
 * @route '/service/reorder'
 */
    const reorderForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: reorder.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\ServiceController::reorder
 * @see app/Http/Controllers/ServiceController.php:635
 * @route '/service/reorder'
 */
        reorderForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: reorder.url(options),
            method: 'post',
        })
    
    reorder.form = reorderForm
const ServiceController = { index, getdata, create, store, edit, update, destroy, updateStatus, categoryGetdata, categoryStatusUpdate, categoryStore, categoryUpdate, categoryDestroy, variantGetdata, variantStore, variantUpdate, variantDestroy, reorder }

export default ServiceController

import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\TierController::index
 * @see app/Http/Controllers/TierController.php:25
 * @route '/loyalty-program'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/loyalty-program',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\TierController::index
 * @see app/Http/Controllers/TierController.php:25
 * @route '/loyalty-program'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\TierController::index
 * @see app/Http/Controllers/TierController.php:25
 * @route '/loyalty-program'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\TierController::index
 * @see app/Http/Controllers/TierController.php:25
 * @route '/loyalty-program'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\TierController::index
 * @see app/Http/Controllers/TierController.php:25
 * @route '/loyalty-program'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\TierController::index
 * @see app/Http/Controllers/TierController.php:25
 * @route '/loyalty-program'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\TierController::index
 * @see app/Http/Controllers/TierController.php:25
 * @route '/loyalty-program'
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
* @see \App\Http\Controllers\TierController::getdata
 * @see app/Http/Controllers/TierController.php:0
 * @route '/loyalty-program/getdata'
 */
export const getdata = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getdata.url(options),
    method: 'get',
})

getdata.definition = {
    methods: ["get","head"],
    url: '/loyalty-program/getdata',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\TierController::getdata
 * @see app/Http/Controllers/TierController.php:0
 * @route '/loyalty-program/getdata'
 */
getdata.url = (options?: RouteQueryOptions) => {
    return getdata.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\TierController::getdata
 * @see app/Http/Controllers/TierController.php:0
 * @route '/loyalty-program/getdata'
 */
getdata.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getdata.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\TierController::getdata
 * @see app/Http/Controllers/TierController.php:0
 * @route '/loyalty-program/getdata'
 */
getdata.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getdata.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\TierController::getdata
 * @see app/Http/Controllers/TierController.php:0
 * @route '/loyalty-program/getdata'
 */
    const getdataForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: getdata.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\TierController::getdata
 * @see app/Http/Controllers/TierController.php:0
 * @route '/loyalty-program/getdata'
 */
        getdataForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getdata.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\TierController::getdata
 * @see app/Http/Controllers/TierController.php:0
 * @route '/loyalty-program/getdata'
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
* @see \App\Http\Controllers\TierController::create
 * @see app/Http/Controllers/TierController.php:33
 * @route '/loyalty-program/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/loyalty-program/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\TierController::create
 * @see app/Http/Controllers/TierController.php:33
 * @route '/loyalty-program/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\TierController::create
 * @see app/Http/Controllers/TierController.php:33
 * @route '/loyalty-program/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\TierController::create
 * @see app/Http/Controllers/TierController.php:33
 * @route '/loyalty-program/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\TierController::create
 * @see app/Http/Controllers/TierController.php:33
 * @route '/loyalty-program/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\TierController::create
 * @see app/Http/Controllers/TierController.php:33
 * @route '/loyalty-program/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\TierController::create
 * @see app/Http/Controllers/TierController.php:33
 * @route '/loyalty-program/create'
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
* @see \App\Http\Controllers\TierController::store
 * @see app/Http/Controllers/TierController.php:41
 * @route '/loyalty-program/store'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/loyalty-program/store',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\TierController::store
 * @see app/Http/Controllers/TierController.php:41
 * @route '/loyalty-program/store'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\TierController::store
 * @see app/Http/Controllers/TierController.php:41
 * @route '/loyalty-program/store'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\TierController::store
 * @see app/Http/Controllers/TierController.php:41
 * @route '/loyalty-program/store'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\TierController::store
 * @see app/Http/Controllers/TierController.php:41
 * @route '/loyalty-program/store'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\TierController::edit
 * @see app/Http/Controllers/TierController.php:57
 * @route '/loyalty-program/edit/{id}'
 */
export const edit = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/loyalty-program/edit/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\TierController::edit
 * @see app/Http/Controllers/TierController.php:57
 * @route '/loyalty-program/edit/{id}'
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
* @see \App\Http\Controllers\TierController::edit
 * @see app/Http/Controllers/TierController.php:57
 * @route '/loyalty-program/edit/{id}'
 */
edit.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\TierController::edit
 * @see app/Http/Controllers/TierController.php:57
 * @route '/loyalty-program/edit/{id}'
 */
edit.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\TierController::edit
 * @see app/Http/Controllers/TierController.php:57
 * @route '/loyalty-program/edit/{id}'
 */
    const editForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\TierController::edit
 * @see app/Http/Controllers/TierController.php:57
 * @route '/loyalty-program/edit/{id}'
 */
        editForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\TierController::edit
 * @see app/Http/Controllers/TierController.php:57
 * @route '/loyalty-program/edit/{id}'
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
* @see \App\Http\Controllers\TierController::update
 * @see app/Http/Controllers/TierController.php:65
 * @route '/loyalty-program/update'
 */
export const update = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

update.definition = {
    methods: ["post"],
    url: '/loyalty-program/update',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\TierController::update
 * @see app/Http/Controllers/TierController.php:65
 * @route '/loyalty-program/update'
 */
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\TierController::update
 * @see app/Http/Controllers/TierController.php:65
 * @route '/loyalty-program/update'
 */
update.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\TierController::update
 * @see app/Http/Controllers/TierController.php:65
 * @route '/loyalty-program/update'
 */
    const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\TierController::update
 * @see app/Http/Controllers/TierController.php:65
 * @route '/loyalty-program/update'
 */
        updateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(options),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\TierController::destroy
 * @see app/Http/Controllers/TierController.php:101
 * @route '/loyalty-program/delete'
 */
export const destroy = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: destroy.url(options),
    method: 'post',
})

destroy.definition = {
    methods: ["post"],
    url: '/loyalty-program/delete',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\TierController::destroy
 * @see app/Http/Controllers/TierController.php:101
 * @route '/loyalty-program/delete'
 */
destroy.url = (options?: RouteQueryOptions) => {
    return destroy.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\TierController::destroy
 * @see app/Http/Controllers/TierController.php:101
 * @route '/loyalty-program/delete'
 */
destroy.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: destroy.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\TierController::destroy
 * @see app/Http/Controllers/TierController.php:101
 * @route '/loyalty-program/delete'
 */
    const destroyForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\TierController::destroy
 * @see app/Http/Controllers/TierController.php:101
 * @route '/loyalty-program/delete'
 */
        destroyForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(options),
            method: 'post',
        })
    
    destroy.form = destroyForm
/**
* @see \App\Http\Controllers\TierController::updateStatus
 * @see app/Http/Controllers/TierController.php:0
 * @route '/loyalty-program/status/update'
 */
export const updateStatus = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateStatus.url(options),
    method: 'put',
})

updateStatus.definition = {
    methods: ["put"],
    url: '/loyalty-program/status/update',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\TierController::updateStatus
 * @see app/Http/Controllers/TierController.php:0
 * @route '/loyalty-program/status/update'
 */
updateStatus.url = (options?: RouteQueryOptions) => {
    return updateStatus.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\TierController::updateStatus
 * @see app/Http/Controllers/TierController.php:0
 * @route '/loyalty-program/status/update'
 */
updateStatus.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateStatus.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\TierController::updateStatus
 * @see app/Http/Controllers/TierController.php:0
 * @route '/loyalty-program/status/update'
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
* @see \App\Http\Controllers\TierController::updateStatus
 * @see app/Http/Controllers/TierController.php:0
 * @route '/loyalty-program/status/update'
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
const TierController = { index, getdata, create, store, edit, update, destroy, updateStatus }

export default TierController

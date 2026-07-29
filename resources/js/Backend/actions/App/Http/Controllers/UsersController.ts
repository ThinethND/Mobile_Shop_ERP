import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\UsersController::index
 * @see app/Http/Controllers/UsersController.php:25
 * @route '/users'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/users',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\UsersController::index
 * @see app/Http/Controllers/UsersController.php:25
 * @route '/users'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\UsersController::index
 * @see app/Http/Controllers/UsersController.php:25
 * @route '/users'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\UsersController::index
 * @see app/Http/Controllers/UsersController.php:25
 * @route '/users'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\UsersController::index
 * @see app/Http/Controllers/UsersController.php:25
 * @route '/users'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\UsersController::index
 * @see app/Http/Controllers/UsersController.php:25
 * @route '/users'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\UsersController::index
 * @see app/Http/Controllers/UsersController.php:25
 * @route '/users'
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
* @see \App\Http\Controllers\UsersController::getData
 * @see app/Http/Controllers/UsersController.php:88
 * @route '/users/get/data'
 */
export const getData = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getData.url(options),
    method: 'get',
})

getData.definition = {
    methods: ["get","head"],
    url: '/users/get/data',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\UsersController::getData
 * @see app/Http/Controllers/UsersController.php:88
 * @route '/users/get/data'
 */
getData.url = (options?: RouteQueryOptions) => {
    return getData.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\UsersController::getData
 * @see app/Http/Controllers/UsersController.php:88
 * @route '/users/get/data'
 */
getData.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getData.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\UsersController::getData
 * @see app/Http/Controllers/UsersController.php:88
 * @route '/users/get/data'
 */
getData.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getData.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\UsersController::getData
 * @see app/Http/Controllers/UsersController.php:88
 * @route '/users/get/data'
 */
    const getDataForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: getData.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\UsersController::getData
 * @see app/Http/Controllers/UsersController.php:88
 * @route '/users/get/data'
 */
        getDataForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getData.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\UsersController::getData
 * @see app/Http/Controllers/UsersController.php:88
 * @route '/users/get/data'
 */
        getDataForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getData.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    getData.form = getDataForm
/**
* @see \App\Http\Controllers\UsersController::create
 * @see app/Http/Controllers/UsersController.php:33
 * @route '/users/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/users/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\UsersController::create
 * @see app/Http/Controllers/UsersController.php:33
 * @route '/users/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\UsersController::create
 * @see app/Http/Controllers/UsersController.php:33
 * @route '/users/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\UsersController::create
 * @see app/Http/Controllers/UsersController.php:33
 * @route '/users/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\UsersController::create
 * @see app/Http/Controllers/UsersController.php:33
 * @route '/users/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\UsersController::create
 * @see app/Http/Controllers/UsersController.php:33
 * @route '/users/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\UsersController::create
 * @see app/Http/Controllers/UsersController.php:33
 * @route '/users/create'
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
* @see \App\Http\Controllers\UsersController::store
 * @see app/Http/Controllers/UsersController.php:44
 * @route '/users/store'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/users/store',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\UsersController::store
 * @see app/Http/Controllers/UsersController.php:44
 * @route '/users/store'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\UsersController::store
 * @see app/Http/Controllers/UsersController.php:44
 * @route '/users/store'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\UsersController::store
 * @see app/Http/Controllers/UsersController.php:44
 * @route '/users/store'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\UsersController::store
 * @see app/Http/Controllers/UsersController.php:44
 * @route '/users/store'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\UsersController::edit
 * @see app/Http/Controllers/UsersController.php:172
 * @route '/users/edit/{id}'
 */
export const edit = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/users/edit/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\UsersController::edit
 * @see app/Http/Controllers/UsersController.php:172
 * @route '/users/edit/{id}'
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
* @see \App\Http\Controllers\UsersController::edit
 * @see app/Http/Controllers/UsersController.php:172
 * @route '/users/edit/{id}'
 */
edit.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\UsersController::edit
 * @see app/Http/Controllers/UsersController.php:172
 * @route '/users/edit/{id}'
 */
edit.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\UsersController::edit
 * @see app/Http/Controllers/UsersController.php:172
 * @route '/users/edit/{id}'
 */
    const editForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\UsersController::edit
 * @see app/Http/Controllers/UsersController.php:172
 * @route '/users/edit/{id}'
 */
        editForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\UsersController::edit
 * @see app/Http/Controllers/UsersController.php:172
 * @route '/users/edit/{id}'
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
* @see \App\Http\Controllers\UsersController::update
 * @see app/Http/Controllers/UsersController.php:210
 * @route '/users/update'
 */
export const update = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

update.definition = {
    methods: ["post"],
    url: '/users/update',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\UsersController::update
 * @see app/Http/Controllers/UsersController.php:210
 * @route '/users/update'
 */
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\UsersController::update
 * @see app/Http/Controllers/UsersController.php:210
 * @route '/users/update'
 */
update.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\UsersController::update
 * @see app/Http/Controllers/UsersController.php:210
 * @route '/users/update'
 */
    const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\UsersController::update
 * @see app/Http/Controllers/UsersController.php:210
 * @route '/users/update'
 */
        updateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(options),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\UsersController::updateStatus
 * @see app/Http/Controllers/UsersController.php:187
 * @route '/users/update/status'
 */
export const updateStatus = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateStatus.url(options),
    method: 'put',
})

updateStatus.definition = {
    methods: ["put"],
    url: '/users/update/status',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\UsersController::updateStatus
 * @see app/Http/Controllers/UsersController.php:187
 * @route '/users/update/status'
 */
updateStatus.url = (options?: RouteQueryOptions) => {
    return updateStatus.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\UsersController::updateStatus
 * @see app/Http/Controllers/UsersController.php:187
 * @route '/users/update/status'
 */
updateStatus.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateStatus.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\UsersController::updateStatus
 * @see app/Http/Controllers/UsersController.php:187
 * @route '/users/update/status'
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
* @see \App\Http\Controllers\UsersController::updateStatus
 * @see app/Http/Controllers/UsersController.php:187
 * @route '/users/update/status'
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
* @see \App\Http\Controllers\UsersController::updatePassword
 * @see app/Http/Controllers/UsersController.php:270
 * @route '/users/update/password'
 */
export const updatePassword = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updatePassword.url(options),
    method: 'put',
})

updatePassword.definition = {
    methods: ["put"],
    url: '/users/update/password',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\UsersController::updatePassword
 * @see app/Http/Controllers/UsersController.php:270
 * @route '/users/update/password'
 */
updatePassword.url = (options?: RouteQueryOptions) => {
    return updatePassword.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\UsersController::updatePassword
 * @see app/Http/Controllers/UsersController.php:270
 * @route '/users/update/password'
 */
updatePassword.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updatePassword.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\UsersController::updatePassword
 * @see app/Http/Controllers/UsersController.php:270
 * @route '/users/update/password'
 */
    const updatePasswordForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updatePassword.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\UsersController::updatePassword
 * @see app/Http/Controllers/UsersController.php:270
 * @route '/users/update/password'
 */
        updatePasswordForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updatePassword.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updatePassword.form = updatePasswordForm
/**
* @see \App\Http\Controllers\UsersController::softDelete
 * @see app/Http/Controllers/UsersController.php:0
 * @route '/users/suspend'
 */
export const softDelete = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: softDelete.url(options),
    method: 'post',
})

softDelete.definition = {
    methods: ["post"],
    url: '/users/suspend',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\UsersController::softDelete
 * @see app/Http/Controllers/UsersController.php:0
 * @route '/users/suspend'
 */
softDelete.url = (options?: RouteQueryOptions) => {
    return softDelete.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\UsersController::softDelete
 * @see app/Http/Controllers/UsersController.php:0
 * @route '/users/suspend'
 */
softDelete.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: softDelete.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\UsersController::softDelete
 * @see app/Http/Controllers/UsersController.php:0
 * @route '/users/suspend'
 */
    const softDeleteForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: softDelete.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\UsersController::softDelete
 * @see app/Http/Controllers/UsersController.php:0
 * @route '/users/suspend'
 */
        softDeleteForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: softDelete.url(options),
            method: 'post',
        })
    
    softDelete.form = softDeleteForm
/**
* @see \App\Http\Controllers\UsersController::restore
 * @see app/Http/Controllers/UsersController.php:0
 * @route '/users/restore'
 */
export const restore = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore.url(options),
    method: 'post',
})

restore.definition = {
    methods: ["post"],
    url: '/users/restore',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\UsersController::restore
 * @see app/Http/Controllers/UsersController.php:0
 * @route '/users/restore'
 */
restore.url = (options?: RouteQueryOptions) => {
    return restore.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\UsersController::restore
 * @see app/Http/Controllers/UsersController.php:0
 * @route '/users/restore'
 */
restore.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\UsersController::restore
 * @see app/Http/Controllers/UsersController.php:0
 * @route '/users/restore'
 */
    const restoreForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restore.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\UsersController::restore
 * @see app/Http/Controllers/UsersController.php:0
 * @route '/users/restore'
 */
        restoreForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restore.url(options),
            method: 'post',
        })
    
    restore.form = restoreForm
/**
* @see \App\Http\Controllers\UsersController::deleteMethod
 * @see app/Http/Controllers/UsersController.php:254
 * @route '/users/delete'
 */
export const deleteMethod = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: deleteMethod.url(options),
    method: 'post',
})

deleteMethod.definition = {
    methods: ["post"],
    url: '/users/delete',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\UsersController::deleteMethod
 * @see app/Http/Controllers/UsersController.php:254
 * @route '/users/delete'
 */
deleteMethod.url = (options?: RouteQueryOptions) => {
    return deleteMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\UsersController::deleteMethod
 * @see app/Http/Controllers/UsersController.php:254
 * @route '/users/delete'
 */
deleteMethod.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: deleteMethod.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\UsersController::deleteMethod
 * @see app/Http/Controllers/UsersController.php:254
 * @route '/users/delete'
 */
    const deleteMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: deleteMethod.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\UsersController::deleteMethod
 * @see app/Http/Controllers/UsersController.php:254
 * @route '/users/delete'
 */
        deleteMethodForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: deleteMethod.url(options),
            method: 'post',
        })
    
    deleteMethod.form = deleteMethodForm
/**
* @see \App\Http\Controllers\UsersController::orderList
 * @see app/Http/Controllers/UsersController.php:297
 * @route '/users/order/list'
 */
export const orderList = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: orderList.url(options),
    method: 'get',
})

orderList.definition = {
    methods: ["get","head"],
    url: '/users/order/list',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\UsersController::orderList
 * @see app/Http/Controllers/UsersController.php:297
 * @route '/users/order/list'
 */
orderList.url = (options?: RouteQueryOptions) => {
    return orderList.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\UsersController::orderList
 * @see app/Http/Controllers/UsersController.php:297
 * @route '/users/order/list'
 */
orderList.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: orderList.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\UsersController::orderList
 * @see app/Http/Controllers/UsersController.php:297
 * @route '/users/order/list'
 */
orderList.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: orderList.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\UsersController::orderList
 * @see app/Http/Controllers/UsersController.php:297
 * @route '/users/order/list'
 */
    const orderListForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: orderList.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\UsersController::orderList
 * @see app/Http/Controllers/UsersController.php:297
 * @route '/users/order/list'
 */
        orderListForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: orderList.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\UsersController::orderList
 * @see app/Http/Controllers/UsersController.php:297
 * @route '/users/order/list'
 */
        orderListForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: orderList.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    orderList.form = orderListForm
/**
* @see \App\Http\Controllers\UsersController::orderUpdate
 * @see app/Http/Controllers/UsersController.php:305
 * @route '/users/order/update'
 */
export const orderUpdate = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: orderUpdate.url(options),
    method: 'post',
})

orderUpdate.definition = {
    methods: ["post"],
    url: '/users/order/update',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\UsersController::orderUpdate
 * @see app/Http/Controllers/UsersController.php:305
 * @route '/users/order/update'
 */
orderUpdate.url = (options?: RouteQueryOptions) => {
    return orderUpdate.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\UsersController::orderUpdate
 * @see app/Http/Controllers/UsersController.php:305
 * @route '/users/order/update'
 */
orderUpdate.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: orderUpdate.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\UsersController::orderUpdate
 * @see app/Http/Controllers/UsersController.php:305
 * @route '/users/order/update'
 */
    const orderUpdateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: orderUpdate.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\UsersController::orderUpdate
 * @see app/Http/Controllers/UsersController.php:305
 * @route '/users/order/update'
 */
        orderUpdateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: orderUpdate.url(options),
            method: 'post',
        })
    
    orderUpdate.form = orderUpdateForm
const UsersController = { index, getData, create, store, edit, update, updateStatus, updatePassword, softDelete, restore, deleteMethod, orderList, orderUpdate, delete: deleteMethod }

export default UsersController

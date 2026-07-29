import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\RoleController::index
 * @see app/Http/Controllers/RoleController.php:21
 * @route '/roles'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/roles',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\RoleController::index
 * @see app/Http/Controllers/RoleController.php:21
 * @route '/roles'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\RoleController::index
 * @see app/Http/Controllers/RoleController.php:21
 * @route '/roles'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\RoleController::index
 * @see app/Http/Controllers/RoleController.php:21
 * @route '/roles'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\RoleController::index
 * @see app/Http/Controllers/RoleController.php:21
 * @route '/roles'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\RoleController::index
 * @see app/Http/Controllers/RoleController.php:21
 * @route '/roles'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\RoleController::index
 * @see app/Http/Controllers/RoleController.php:21
 * @route '/roles'
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
* @see \App\Http\Controllers\RoleController::getdata
 * @see app/Http/Controllers/RoleController.php:26
 * @route '/roles/getdata'
 */
export const getdata = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getdata.url(options),
    method: 'get',
})

getdata.definition = {
    methods: ["get","head"],
    url: '/roles/getdata',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\RoleController::getdata
 * @see app/Http/Controllers/RoleController.php:26
 * @route '/roles/getdata'
 */
getdata.url = (options?: RouteQueryOptions) => {
    return getdata.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\RoleController::getdata
 * @see app/Http/Controllers/RoleController.php:26
 * @route '/roles/getdata'
 */
getdata.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getdata.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\RoleController::getdata
 * @see app/Http/Controllers/RoleController.php:26
 * @route '/roles/getdata'
 */
getdata.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getdata.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\RoleController::getdata
 * @see app/Http/Controllers/RoleController.php:26
 * @route '/roles/getdata'
 */
    const getdataForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: getdata.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\RoleController::getdata
 * @see app/Http/Controllers/RoleController.php:26
 * @route '/roles/getdata'
 */
        getdataForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getdata.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\RoleController::getdata
 * @see app/Http/Controllers/RoleController.php:26
 * @route '/roles/getdata'
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
* @see \App\Http\Controllers\RoleController::create
 * @see app/Http/Controllers/RoleController.php:56
 * @route '/roles/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/roles/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\RoleController::create
 * @see app/Http/Controllers/RoleController.php:56
 * @route '/roles/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\RoleController::create
 * @see app/Http/Controllers/RoleController.php:56
 * @route '/roles/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\RoleController::create
 * @see app/Http/Controllers/RoleController.php:56
 * @route '/roles/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\RoleController::create
 * @see app/Http/Controllers/RoleController.php:56
 * @route '/roles/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\RoleController::create
 * @see app/Http/Controllers/RoleController.php:56
 * @route '/roles/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\RoleController::create
 * @see app/Http/Controllers/RoleController.php:56
 * @route '/roles/create'
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
* @see \App\Http\Controllers\RoleController::store
 * @see app/Http/Controllers/RoleController.php:69
 * @route '/roles/store'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/roles/store',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\RoleController::store
 * @see app/Http/Controllers/RoleController.php:69
 * @route '/roles/store'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\RoleController::store
 * @see app/Http/Controllers/RoleController.php:69
 * @route '/roles/store'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\RoleController::store
 * @see app/Http/Controllers/RoleController.php:69
 * @route '/roles/store'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\RoleController::store
 * @see app/Http/Controllers/RoleController.php:69
 * @route '/roles/store'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\RoleController::edit
 * @see app/Http/Controllers/RoleController.php:105
 * @route '/roles/edit/{id}'
 */
export const edit = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/roles/edit/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\RoleController::edit
 * @see app/Http/Controllers/RoleController.php:105
 * @route '/roles/edit/{id}'
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
* @see \App\Http\Controllers\RoleController::edit
 * @see app/Http/Controllers/RoleController.php:105
 * @route '/roles/edit/{id}'
 */
edit.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\RoleController::edit
 * @see app/Http/Controllers/RoleController.php:105
 * @route '/roles/edit/{id}'
 */
edit.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\RoleController::edit
 * @see app/Http/Controllers/RoleController.php:105
 * @route '/roles/edit/{id}'
 */
    const editForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\RoleController::edit
 * @see app/Http/Controllers/RoleController.php:105
 * @route '/roles/edit/{id}'
 */
        editForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\RoleController::edit
 * @see app/Http/Controllers/RoleController.php:105
 * @route '/roles/edit/{id}'
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
* @see \App\Http\Controllers\RoleController::update
 * @see app/Http/Controllers/RoleController.php:118
 * @route '/roles/update'
 */
export const update = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

update.definition = {
    methods: ["post"],
    url: '/roles/update',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\RoleController::update
 * @see app/Http/Controllers/RoleController.php:118
 * @route '/roles/update'
 */
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\RoleController::update
 * @see app/Http/Controllers/RoleController.php:118
 * @route '/roles/update'
 */
update.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\RoleController::update
 * @see app/Http/Controllers/RoleController.php:118
 * @route '/roles/update'
 */
    const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\RoleController::update
 * @see app/Http/Controllers/RoleController.php:118
 * @route '/roles/update'
 */
        updateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(options),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\RoleController::editPermissions
 * @see app/Http/Controllers/RoleController.php:147
 * @route '/roles/permissions/{id}'
 */
export const editPermissions = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: editPermissions.url(args, options),
    method: 'get',
})

editPermissions.definition = {
    methods: ["get","head"],
    url: '/roles/permissions/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\RoleController::editPermissions
 * @see app/Http/Controllers/RoleController.php:147
 * @route '/roles/permissions/{id}'
 */
editPermissions.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return editPermissions.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\RoleController::editPermissions
 * @see app/Http/Controllers/RoleController.php:147
 * @route '/roles/permissions/{id}'
 */
editPermissions.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: editPermissions.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\RoleController::editPermissions
 * @see app/Http/Controllers/RoleController.php:147
 * @route '/roles/permissions/{id}'
 */
editPermissions.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: editPermissions.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\RoleController::editPermissions
 * @see app/Http/Controllers/RoleController.php:147
 * @route '/roles/permissions/{id}'
 */
    const editPermissionsForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: editPermissions.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\RoleController::editPermissions
 * @see app/Http/Controllers/RoleController.php:147
 * @route '/roles/permissions/{id}'
 */
        editPermissionsForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: editPermissions.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\RoleController::editPermissions
 * @see app/Http/Controllers/RoleController.php:147
 * @route '/roles/permissions/{id}'
 */
        editPermissionsForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: editPermissions.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    editPermissions.form = editPermissionsForm
/**
* @see \App\Http\Controllers\RoleController::updatePermissions
 * @see app/Http/Controllers/RoleController.php:161
 * @route '/roles/permissions/update'
 */
export const updatePermissions = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updatePermissions.url(options),
    method: 'post',
})

updatePermissions.definition = {
    methods: ["post"],
    url: '/roles/permissions/update',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\RoleController::updatePermissions
 * @see app/Http/Controllers/RoleController.php:161
 * @route '/roles/permissions/update'
 */
updatePermissions.url = (options?: RouteQueryOptions) => {
    return updatePermissions.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\RoleController::updatePermissions
 * @see app/Http/Controllers/RoleController.php:161
 * @route '/roles/permissions/update'
 */
updatePermissions.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updatePermissions.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\RoleController::updatePermissions
 * @see app/Http/Controllers/RoleController.php:161
 * @route '/roles/permissions/update'
 */
    const updatePermissionsForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updatePermissions.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\RoleController::updatePermissions
 * @see app/Http/Controllers/RoleController.php:161
 * @route '/roles/permissions/update'
 */
        updatePermissionsForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updatePermissions.url(options),
            method: 'post',
        })
    
    updatePermissions.form = updatePermissionsForm
/**
* @see \App\Http\Controllers\RoleController::destroy
 * @see app/Http/Controllers/RoleController.php:184
 * @route '/roles/delete/{id}'
 */
export const destroy = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/roles/delete/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\RoleController::destroy
 * @see app/Http/Controllers/RoleController.php:184
 * @route '/roles/delete/{id}'
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
* @see \App\Http\Controllers\RoleController::destroy
 * @see app/Http/Controllers/RoleController.php:184
 * @route '/roles/delete/{id}'
 */
destroy.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\RoleController::destroy
 * @see app/Http/Controllers/RoleController.php:184
 * @route '/roles/delete/{id}'
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
* @see \App\Http\Controllers\RoleController::destroy
 * @see app/Http/Controllers/RoleController.php:184
 * @route '/roles/delete/{id}'
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
const RoleController = { index, getdata, create, store, edit, update, editPermissions, updatePermissions, destroy }

export default RoleController

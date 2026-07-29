import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\V1\BranchController::getBranches
 * @see app/Http/Controllers/Api/V1/BranchController.php:23
 * @route '/api/v1/branches/get-branches'
 */
export const getBranches = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getBranches.url(options),
    method: 'get',
})

getBranches.definition = {
    methods: ["get","head"],
    url: '/api/v1/branches/get-branches',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\BranchController::getBranches
 * @see app/Http/Controllers/Api/V1/BranchController.php:23
 * @route '/api/v1/branches/get-branches'
 */
getBranches.url = (options?: RouteQueryOptions) => {
    return getBranches.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\BranchController::getBranches
 * @see app/Http/Controllers/Api/V1/BranchController.php:23
 * @route '/api/v1/branches/get-branches'
 */
getBranches.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getBranches.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\BranchController::getBranches
 * @see app/Http/Controllers/Api/V1/BranchController.php:23
 * @route '/api/v1/branches/get-branches'
 */
getBranches.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getBranches.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\BranchController::getBranches
 * @see app/Http/Controllers/Api/V1/BranchController.php:23
 * @route '/api/v1/branches/get-branches'
 */
    const getBranchesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: getBranches.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\BranchController::getBranches
 * @see app/Http/Controllers/Api/V1/BranchController.php:23
 * @route '/api/v1/branches/get-branches'
 */
        getBranchesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getBranches.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\BranchController::getBranches
 * @see app/Http/Controllers/Api/V1/BranchController.php:23
 * @route '/api/v1/branches/get-branches'
 */
        getBranchesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getBranches.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    getBranches.form = getBranchesForm
/**
* @see \App\Http\Controllers\Api\V1\BranchController::getBranchTeam
 * @see app/Http/Controllers/Api/V1/BranchController.php:34
 * @route '/api/v1/branches/get-branches-staff'
 */
export const getBranchTeam = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: getBranchTeam.url(options),
    method: 'post',
})

getBranchTeam.definition = {
    methods: ["post"],
    url: '/api/v1/branches/get-branches-staff',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\BranchController::getBranchTeam
 * @see app/Http/Controllers/Api/V1/BranchController.php:34
 * @route '/api/v1/branches/get-branches-staff'
 */
getBranchTeam.url = (options?: RouteQueryOptions) => {
    return getBranchTeam.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\BranchController::getBranchTeam
 * @see app/Http/Controllers/Api/V1/BranchController.php:34
 * @route '/api/v1/branches/get-branches-staff'
 */
getBranchTeam.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: getBranchTeam.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\BranchController::getBranchTeam
 * @see app/Http/Controllers/Api/V1/BranchController.php:34
 * @route '/api/v1/branches/get-branches-staff'
 */
    const getBranchTeamForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: getBranchTeam.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\BranchController::getBranchTeam
 * @see app/Http/Controllers/Api/V1/BranchController.php:34
 * @route '/api/v1/branches/get-branches-staff'
 */
        getBranchTeamForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: getBranchTeam.url(options),
            method: 'post',
        })
    
    getBranchTeam.form = getBranchTeamForm
const BranchController = { getBranches, getBranchTeam }

export default BranchController

import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::register
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:216
 * @route '/api/v1/auth/client-register'
 */
export const register = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: register.url(options),
    method: 'post',
})

register.definition = {
    methods: ["post"],
    url: '/api/v1/auth/client-register',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::register
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:216
 * @route '/api/v1/auth/client-register'
 */
register.url = (options?: RouteQueryOptions) => {
    return register.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::register
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:216
 * @route '/api/v1/auth/client-register'
 */
register.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: register.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::register
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:216
 * @route '/api/v1/auth/client-register'
 */
    const registerForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: register.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::register
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:216
 * @route '/api/v1/auth/client-register'
 */
        registerForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: register.url(options),
            method: 'post',
        })
    
    register.form = registerForm
/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::login
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:38
 * @route '/api/v1/auth/client-login'
 */
export const login = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

login.definition = {
    methods: ["post"],
    url: '/api/v1/auth/client-login',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::login
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:38
 * @route '/api/v1/auth/client-login'
 */
login.url = (options?: RouteQueryOptions) => {
    return login.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::login
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:38
 * @route '/api/v1/auth/client-login'
 */
login.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::login
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:38
 * @route '/api/v1/auth/client-login'
 */
    const loginForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: login.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::login
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:38
 * @route '/api/v1/auth/client-login'
 */
        loginForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: login.url(options),
            method: 'post',
        })
    
    login.form = loginForm
/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::loginWithGoogle
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:78
 * @route '/api/v1/auth/login/social/google'
 */
export const loginWithGoogle = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: loginWithGoogle.url(options),
    method: 'post',
})

loginWithGoogle.definition = {
    methods: ["post"],
    url: '/api/v1/auth/login/social/google',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::loginWithGoogle
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:78
 * @route '/api/v1/auth/login/social/google'
 */
loginWithGoogle.url = (options?: RouteQueryOptions) => {
    return loginWithGoogle.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::loginWithGoogle
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:78
 * @route '/api/v1/auth/login/social/google'
 */
loginWithGoogle.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: loginWithGoogle.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::loginWithGoogle
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:78
 * @route '/api/v1/auth/login/social/google'
 */
    const loginWithGoogleForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: loginWithGoogle.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::loginWithGoogle
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:78
 * @route '/api/v1/auth/login/social/google'
 */
        loginWithGoogleForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: loginWithGoogle.url(options),
            method: 'post',
        })
    
    loginWithGoogle.form = loginWithGoogleForm
/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::loginWithApple
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:121
 * @route '/api/v1/auth/login/social/apple'
 */
export const loginWithApple = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: loginWithApple.url(options),
    method: 'post',
})

loginWithApple.definition = {
    methods: ["post"],
    url: '/api/v1/auth/login/social/apple',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::loginWithApple
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:121
 * @route '/api/v1/auth/login/social/apple'
 */
loginWithApple.url = (options?: RouteQueryOptions) => {
    return loginWithApple.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::loginWithApple
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:121
 * @route '/api/v1/auth/login/social/apple'
 */
loginWithApple.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: loginWithApple.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::loginWithApple
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:121
 * @route '/api/v1/auth/login/social/apple'
 */
    const loginWithAppleForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: loginWithApple.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::loginWithApple
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:121
 * @route '/api/v1/auth/login/social/apple'
 */
        loginWithAppleForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: loginWithApple.url(options),
            method: 'post',
        })
    
    loginWithApple.form = loginWithAppleForm
/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::status
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:33
 * @route '/api/v1/auth/check-status'
 */
export const status = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: status.url(options),
    method: 'get',
})

status.definition = {
    methods: ["get","head"],
    url: '/api/v1/auth/check-status',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::status
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:33
 * @route '/api/v1/auth/check-status'
 */
status.url = (options?: RouteQueryOptions) => {
    return status.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::status
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:33
 * @route '/api/v1/auth/check-status'
 */
status.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: status.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::status
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:33
 * @route '/api/v1/auth/check-status'
 */
status.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: status.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::status
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:33
 * @route '/api/v1/auth/check-status'
 */
    const statusForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: status.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::status
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:33
 * @route '/api/v1/auth/check-status'
 */
        statusForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: status.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::status
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:33
 * @route '/api/v1/auth/check-status'
 */
        statusForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: status.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    status.form = statusForm
/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::sendWelcomeMessage
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:690
 * @route '/api/v1/auth/send-welcome-sms'
 */
export const sendWelcomeMessage = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: sendWelcomeMessage.url(options),
    method: 'get',
})

sendWelcomeMessage.definition = {
    methods: ["get","head"],
    url: '/api/v1/auth/send-welcome-sms',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::sendWelcomeMessage
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:690
 * @route '/api/v1/auth/send-welcome-sms'
 */
sendWelcomeMessage.url = (options?: RouteQueryOptions) => {
    return sendWelcomeMessage.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::sendWelcomeMessage
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:690
 * @route '/api/v1/auth/send-welcome-sms'
 */
sendWelcomeMessage.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: sendWelcomeMessage.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::sendWelcomeMessage
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:690
 * @route '/api/v1/auth/send-welcome-sms'
 */
sendWelcomeMessage.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: sendWelcomeMessage.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::sendWelcomeMessage
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:690
 * @route '/api/v1/auth/send-welcome-sms'
 */
    const sendWelcomeMessageForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: sendWelcomeMessage.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::sendWelcomeMessage
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:690
 * @route '/api/v1/auth/send-welcome-sms'
 */
        sendWelcomeMessageForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: sendWelcomeMessage.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::sendWelcomeMessage
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:690
 * @route '/api/v1/auth/send-welcome-sms'
 */
        sendWelcomeMessageForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: sendWelcomeMessage.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    sendWelcomeMessage.form = sendWelcomeMessageForm
/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::verifyOTP
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:371
 * @route '/api/v1/auth/verify-otp'
 */
export const verifyOTP = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verifyOTP.url(options),
    method: 'post',
})

verifyOTP.definition = {
    methods: ["post"],
    url: '/api/v1/auth/verify-otp',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::verifyOTP
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:371
 * @route '/api/v1/auth/verify-otp'
 */
verifyOTP.url = (options?: RouteQueryOptions) => {
    return verifyOTP.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::verifyOTP
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:371
 * @route '/api/v1/auth/verify-otp'
 */
verifyOTP.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verifyOTP.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::verifyOTP
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:371
 * @route '/api/v1/auth/verify-otp'
 */
    const verifyOTPForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: verifyOTP.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::verifyOTP
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:371
 * @route '/api/v1/auth/verify-otp'
 */
        verifyOTPForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: verifyOTP.url(options),
            method: 'post',
        })
    
    verifyOTP.form = verifyOTPForm
/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::resendOTP
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:359
 * @route '/api/v1/auth/resend-otp'
 */
export const resendOTP = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resendOTP.url(options),
    method: 'post',
})

resendOTP.definition = {
    methods: ["post"],
    url: '/api/v1/auth/resend-otp',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::resendOTP
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:359
 * @route '/api/v1/auth/resend-otp'
 */
resendOTP.url = (options?: RouteQueryOptions) => {
    return resendOTP.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::resendOTP
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:359
 * @route '/api/v1/auth/resend-otp'
 */
resendOTP.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resendOTP.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::resendOTP
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:359
 * @route '/api/v1/auth/resend-otp'
 */
    const resendOTPForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: resendOTP.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::resendOTP
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:359
 * @route '/api/v1/auth/resend-otp'
 */
        resendOTPForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: resendOTP.url(options),
            method: 'post',
        })
    
    resendOTP.form = resendOTPForm
/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::getUserDetails
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:313
 * @route '/api/v1/auth/user-details'
 */
export const getUserDetails = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getUserDetails.url(options),
    method: 'get',
})

getUserDetails.definition = {
    methods: ["get","head"],
    url: '/api/v1/auth/user-details',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::getUserDetails
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:313
 * @route '/api/v1/auth/user-details'
 */
getUserDetails.url = (options?: RouteQueryOptions) => {
    return getUserDetails.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::getUserDetails
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:313
 * @route '/api/v1/auth/user-details'
 */
getUserDetails.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getUserDetails.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::getUserDetails
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:313
 * @route '/api/v1/auth/user-details'
 */
getUserDetails.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getUserDetails.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::getUserDetails
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:313
 * @route '/api/v1/auth/user-details'
 */
    const getUserDetailsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: getUserDetails.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::getUserDetails
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:313
 * @route '/api/v1/auth/user-details'
 */
        getUserDetailsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getUserDetails.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::getUserDetails
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:313
 * @route '/api/v1/auth/user-details'
 */
        getUserDetailsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getUserDetails.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    getUserDetails.form = getUserDetailsForm
/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::logout
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:490
 * @route '/api/v1/auth/logout'
 */
export const logout = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

logout.definition = {
    methods: ["post"],
    url: '/api/v1/auth/logout',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::logout
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:490
 * @route '/api/v1/auth/logout'
 */
logout.url = (options?: RouteQueryOptions) => {
    return logout.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::logout
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:490
 * @route '/api/v1/auth/logout'
 */
logout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::logout
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:490
 * @route '/api/v1/auth/logout'
 */
    const logoutForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: logout.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::logout
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:490
 * @route '/api/v1/auth/logout'
 */
        logoutForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: logout.url(options),
            method: 'post',
        })
    
    logout.form = logoutForm
/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::changePassword
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:593
 * @route '/api/v1/auth/change-password'
 */
export const changePassword = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: changePassword.url(options),
    method: 'post',
})

changePassword.definition = {
    methods: ["post"],
    url: '/api/v1/auth/change-password',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::changePassword
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:593
 * @route '/api/v1/auth/change-password'
 */
changePassword.url = (options?: RouteQueryOptions) => {
    return changePassword.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::changePassword
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:593
 * @route '/api/v1/auth/change-password'
 */
changePassword.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: changePassword.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::changePassword
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:593
 * @route '/api/v1/auth/change-password'
 */
    const changePasswordForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: changePassword.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::changePassword
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:593
 * @route '/api/v1/auth/change-password'
 */
        changePasswordForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: changePassword.url(options),
            method: 'post',
        })
    
    changePassword.form = changePasswordForm
/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::saveVerifiedPassword
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:635
 * @route '/api/v1/auth/save-verified-password'
 */
export const saveVerifiedPassword = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: saveVerifiedPassword.url(options),
    method: 'post',
})

saveVerifiedPassword.definition = {
    methods: ["post"],
    url: '/api/v1/auth/save-verified-password',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::saveVerifiedPassword
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:635
 * @route '/api/v1/auth/save-verified-password'
 */
saveVerifiedPassword.url = (options?: RouteQueryOptions) => {
    return saveVerifiedPassword.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::saveVerifiedPassword
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:635
 * @route '/api/v1/auth/save-verified-password'
 */
saveVerifiedPassword.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: saveVerifiedPassword.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::saveVerifiedPassword
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:635
 * @route '/api/v1/auth/save-verified-password'
 */
    const saveVerifiedPasswordForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: saveVerifiedPassword.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::saveVerifiedPassword
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:635
 * @route '/api/v1/auth/save-verified-password'
 */
        saveVerifiedPasswordForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: saveVerifiedPassword.url(options),
            method: 'post',
        })
    
    saveVerifiedPassword.form = saveVerifiedPasswordForm
/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::updateProfile
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:509
 * @route '/api/v1/auth/update-profile'
 */
export const updateProfile = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updateProfile.url(options),
    method: 'post',
})

updateProfile.definition = {
    methods: ["post"],
    url: '/api/v1/auth/update-profile',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::updateProfile
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:509
 * @route '/api/v1/auth/update-profile'
 */
updateProfile.url = (options?: RouteQueryOptions) => {
    return updateProfile.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::updateProfile
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:509
 * @route '/api/v1/auth/update-profile'
 */
updateProfile.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updateProfile.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::updateProfile
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:509
 * @route '/api/v1/auth/update-profile'
 */
    const updateProfileForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateProfile.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::updateProfile
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:509
 * @route '/api/v1/auth/update-profile'
 */
        updateProfileForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateProfile.url(options),
            method: 'post',
        })
    
    updateProfile.form = updateProfileForm
/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::accountSoftDelete
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:677
 * @route '/api/v1/auth/soft-delete'
 */
export const accountSoftDelete = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: accountSoftDelete.url(options),
    method: 'post',
})

accountSoftDelete.definition = {
    methods: ["post"],
    url: '/api/v1/auth/soft-delete',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::accountSoftDelete
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:677
 * @route '/api/v1/auth/soft-delete'
 */
accountSoftDelete.url = (options?: RouteQueryOptions) => {
    return accountSoftDelete.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::accountSoftDelete
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:677
 * @route '/api/v1/auth/soft-delete'
 */
accountSoftDelete.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: accountSoftDelete.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::accountSoftDelete
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:677
 * @route '/api/v1/auth/soft-delete'
 */
    const accountSoftDeleteForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: accountSoftDelete.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Auth\CustomerAuthController::accountSoftDelete
 * @see app/Http/Controllers/Api/V1/Auth/CustomerAuthController.php:677
 * @route '/api/v1/auth/soft-delete'
 */
        accountSoftDeleteForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: accountSoftDelete.url(options),
            method: 'post',
        })
    
    accountSoftDelete.form = accountSoftDeleteForm
const CustomerAuthController = { register, login, loginWithGoogle, loginWithApple, status, sendWelcomeMessage, verifyOTP, resendOTP, getUserDetails, logout, changePassword, saveVerifiedPassword, updateProfile, accountSoftDelete }

export default CustomerAuthController

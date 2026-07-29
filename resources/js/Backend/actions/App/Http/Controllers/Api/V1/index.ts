import Auth from './Auth'
import CountryCodeController from './CountryCodeController'
import BannerController from './BannerController'
import DiscountController from './DiscountController'
import BranchController from './BranchController'
import ServiceController from './ServiceController'
import BookingsController from './BookingsController'
import LoyaltyTierController from './LoyaltyTierController'
const V1 = {
    Auth: Object.assign(Auth, Auth),
CountryCodeController: Object.assign(CountryCodeController, CountryCodeController),
BannerController: Object.assign(BannerController, BannerController),
DiscountController: Object.assign(DiscountController, DiscountController),
BranchController: Object.assign(BranchController, BranchController),
ServiceController: Object.assign(ServiceController, ServiceController),
BookingsController: Object.assign(BookingsController, BookingsController),
LoyaltyTierController: Object.assign(LoyaltyTierController, LoyaltyTierController),
}

export default V1

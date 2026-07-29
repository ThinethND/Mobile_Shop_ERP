import Api from './Api'
import Settings from './Settings'
import DashboardController from './DashboardController'
import CalendarController from './CalendarController'
import UsersController from './UsersController'
import RoleController from './RoleController'
import BranchController from './BranchController'
import ServiceController from './ServiceController'
import ClientController from './ClientController'
import DiscountController from './DiscountController'
import BannerController from './BannerController'
import TierController from './TierController'
import SmsGatewayController from './SmsGatewayController'
import BookingController from './BookingController'
import ReportController from './ReportController'
import PublicInvoiceController from './PublicInvoiceController'
import AppointmentReportsController from './AppointmentReportsController'
import ClientReportController from './ClientReportController'
import SalesWithTeamController from './SalesWithTeamController'
const Controllers = {
    Api: Object.assign(Api, Api),
Settings: Object.assign(Settings, Settings),
DashboardController: Object.assign(DashboardController, DashboardController),
CalendarController: Object.assign(CalendarController, CalendarController),
UsersController: Object.assign(UsersController, UsersController),
RoleController: Object.assign(RoleController, RoleController),
BranchController: Object.assign(BranchController, BranchController),
ServiceController: Object.assign(ServiceController, ServiceController),
ClientController: Object.assign(ClientController, ClientController),
DiscountController: Object.assign(DiscountController, DiscountController),
BannerController: Object.assign(BannerController, BannerController),
TierController: Object.assign(TierController, TierController),
SmsGatewayController: Object.assign(SmsGatewayController, SmsGatewayController),
BookingController: Object.assign(BookingController, BookingController),
ReportController: Object.assign(ReportController, ReportController),
PublicInvoiceController: Object.assign(PublicInvoiceController, PublicInvoiceController),
AppointmentReportsController: Object.assign(AppointmentReportsController, AppointmentReportsController),
ClientReportController: Object.assign(ClientReportController, ClientReportController),
SalesWithTeamController: Object.assign(SalesWithTeamController, SalesWithTeamController),
}

export default Controllers

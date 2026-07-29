import { createStore } from 'vuex';

export interface SlotInfo {
    dateIso: string | null;
    startIso: string | null;
    endIso: string | null;
}

export interface SelectServiceState {
    show: boolean;
    staff: any | null;
    slot: SlotInfo | null;
    clientMode: 'walk-in' | 'existing';
    clientId: number | null;
    selectedServices: any[];
    appointments: any[];
}

export interface TipMeta {
    bookingId?: number | null;
    booking?: any;
    baseAmount: number;
    client: any | null;
    staff: any | null;
    services: any[];
    currencySymbol: string;

    tipAmount?: number;
    tipMode?: 'none' | 'percent' | 'custom';
    tipPercent?: number | null;
    paymentMethod?: 'cash' | 'gift-card' | 'split' | 'other' | null;
}

export interface TipState {
    show: boolean;
    meta: TipMeta | null;
}

export interface SuccessPaymentState {
    show: boolean;
    meta: any | null;
}

export interface BookingDetailsState {
    show: boolean;
    meta: any | null;
}

export interface RootState {
    selectService: SelectServiceState;
    tip: TipState;
    successPayment: SuccessPaymentState;
    bookingDetails: BookingDetailsState;
}

const store = createStore<RootState>({
    state: {
        selectService: {
            show: false,
            staff: null,
            slot: null,
            clientMode: 'walk-in',
            clientId: null,
            selectedServices: [],
            appointments: [],
        },
        tip: {
            show: false,
            meta: null,
        },
        successPayment: {
            show: false,
            meta: null,
        },
        bookingDetails: {
            show: false,
            meta: null,
        },
    },

    getters: {
        selectServiceShow: (state) => state.selectService.show,
        selectServiceStaff: (state) => state.selectService.staff,
        selectServiceSlot: (state) => state.selectService.slot,
        selectServiceClientMode: (state) => state.selectService.clientMode,
        selectServiceClientId: (state) => state.selectService.clientId,
        selectServiceSelectedServices: (state) =>
            state.selectService.selectedServices,
        selectServiceAppointments: (state) =>
            state.selectService.appointments,

        tipShow: (state) => state.tip.show,
        tipMeta: (state) => state.tip.meta,
        tipAmount: (state) => state.tip.meta?.tipAmount ?? 0,
        tipPaymentMethod: (state) => state.tip.meta?.paymentMethod ?? null,

        successPaymentShow: (state) => state.successPayment.show,
        successPaymentMeta: (state) => state.successPayment.meta,

        bookingDetailsShow: (state) => state.bookingDetails.show,
        bookingDetailsMeta: (state) => state.bookingDetails.meta,
    },

    mutations: {

         SET_SELECT_SERVICE_SLOT_RANGE(
        state,
        payload: { startIso: string | null; endIso: string | null },
    ) {
        if (!state.selectService.slot) {
            state.selectService.slot = {
                dateIso: payload.startIso ? payload.startIso.slice(0, 10) : null,
                startIso: payload.startIso,
                endIso: payload.endIso,
            };
        } else {
            state.selectService.slot.startIso = payload.startIso;
            state.selectService.slot.endIso = payload.endIso;
            if (payload.startIso) {
                state.selectService.slot.dateIso = payload.startIso.slice(0, 10);
            }
        }
    },

    UPDATE_SERVICE_INSTANCE(
        state,
        payload: { index: number; item: any },
    ) {
        const list = state.selectService.selectedServices;
        if (
            !Array.isArray(list) ||
            payload.index < 0 ||
            payload.index >= list.length
        ) {
            return;
        }

        list.splice(payload.index, 1, { ...payload.item });
    },

    SET_SELECT_SERVICE_STAFF(state, staff: any | null) {
        state.selectService.staff = staff ? { ...staff } : null;
    },
 TIP_SET_CLIENT(state, client) {
    state.tip.meta = {
        ...(state.tip.meta || {}),
        client: client ? { ...client } : null,
    };
},


        OPEN_SELECT_SERVICE(
            state,
            payload: {
                staff: any;
                dateIso: string;
                startIso: string;
                endIso: string;
            },
        ) {
            state.selectService.show = true;
            state.selectService.staff = payload.staff || null;
            state.selectService.slot = {
                dateIso: payload.dateIso || null,
                startIso: payload.startIso || null,
                endIso: payload.endIso || null,
            };
            state.selectService.clientMode = 'walk-in';
            state.selectService.clientId = null;
            state.selectService.selectedServices = [];
        },

        CLOSE_SELECT_SERVICE(state) {
            state.selectService.show = false;
        },

        SET_CLIENT_WALKIN(state) {
            state.selectService.clientMode = 'walk-in';
            state.selectService.clientId = null;
        },

        SET_CLIENT_ID(state, id: number) {
            state.selectService.clientMode = 'existing';
            state.selectService.clientId = id;
        },

        TOGGLE_SERVICE(state, row: any) {
            const list = state.selectService.selectedServices;
            const idx = list.findIndex(
                (i: any) =>
                    i.serviceId === row.serviceId &&
                    i.variantId === row.variantId,
            );
            if (idx >= 0) {
                list.splice(idx, 1);
            } else {
                const instanceUid =
                    row.instanceUid ||
                    `svc-${row.serviceId}-${row.variantId ?? 'base'}-toggle`;
                list.push({ ...row, instanceUid });
            }
        },

        ADD_SERVICE_INSTANCE(state, item: any) {
            if (!Array.isArray(state.selectService.selectedServices)) {
                state.selectService.selectedServices = [];
            }
            state.selectService.selectedServices.push({ ...item });
        },

        REMOVE_SERVICE_INSTANCE(state, payload: any) {
            const list = state.selectService.selectedServices;
            if (!Array.isArray(list) || !list.length) return;

            if (payload.instanceUid) {
                const idx = list.findIndex(
                    (i: any) => i.instanceUid === payload.instanceUid,
                );
                if (idx !== -1) {
                    list.splice(idx, 1);
                    return;
                }
            }

            const idx = list.findIndex(
                (i: any) =>
                    i.serviceId === payload.serviceId &&
                    (i.variantId ?? null) === (payload.variantId ?? null),
            );
            if (idx !== -1) {
                list.splice(idx, 1);
            }
        },

        CONFIRM_SELECT_SERVICE(
            state,
            payload?: { mode?: 'save' | 'checkout' },
        ) {
            const s = state.selectService;
            if (!s.slot) return;

            state.selectService.appointments.push({
                staff: s.staff,
                slot: s.slot,
                clientMode: s.clientMode,
                clientId: s.clientId,
                services: [...s.selectedServices],
                mode: payload?.mode || 'save',
            });

            s.show = false;
        },

        OPEN_TIP_PANEL(state, payload: TipMeta | null) {
            state.tip.show = true;
            state.tip.meta = payload || null;
        },

        CLOSE_TIP_PANEL(state) {
            state.tip.show = false;
            state.tip.meta = null;
        },

        UPDATE_TIP_BASE_AMOUNT(state, amount: number) {
            if (!state.tip.meta) return;
            state.tip.meta.baseAmount = amount;
        },

        TIP_UPDATE_SERVICES(state, items: any[]) {
            if (!state.tip.meta) return;
            const formatted = Array.isArray(items) ? items : [];
            state.tip.meta.services = formatted;

            const baseTotal = formatted.reduce((sum: number, item: any) => {
                const price = Number(item.price ?? item.final_price ?? 0);
                const qty = Number(item.quantity ?? 1);
                const safePrice = isNaN(price) ? 0 : price;
                const safeQty = isNaN(qty) || qty <= 0 ? 1 : qty;
                return sum + safePrice * safeQty;
            }, 0);

            state.tip.meta.baseAmount = baseTotal;
        },

        SET_TIP_VALUE(
            state,
            payload: {
                amount: number;
                mode: 'none' | 'percent' | 'custom';
                percent?: number | null;
            },
        ) {
            if (!state.tip.meta) {
                state.tip.meta = {
                    baseAmount: 0,
                    client: null,
                    staff: null,
                    services: [],
                    currencySymbol: '',
                };
            }
            state.tip.meta.tipAmount = payload.amount;
            state.tip.meta.tipMode = payload.mode;
            state.tip.meta.tipPercent =
                typeof payload.percent === 'number' ? payload.percent : null;
        },

        SET_TIP_PAYMENT_METHOD(
            state,
            method: 'cash' | 'gift-card' | 'split' | 'other' | null,
        ) {
            if (!state.tip.meta) return;
            state.tip.meta.paymentMethod = method;
        },

        OPEN_SUCCESS_PAYMENT(state, payload: any) {
            state.successPayment.show = true;
            state.successPayment.meta = payload || null;
        },

        CLOSE_SUCCESS_PAYMENT(state) {
            state.successPayment.show = false;
            state.successPayment.meta = null;
        },

        OPEN_BOOKING_DETAILS(state, payload: any) {
            state.bookingDetails.show = true;
            state.bookingDetails.meta = payload || null;
        },

        CLOSE_BOOKING_DETAILS(state) {
            state.bookingDetails.show = false;
            state.bookingDetails.meta = null;
        },
    },
});

export default store;

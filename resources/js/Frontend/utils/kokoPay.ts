export type KokoPayProduct = {
  display_price?: number | null
  koko_pay_percentage?: number | null
  koko_installment_price?: number | null
}

function positiveNumber(value: number | string | null | undefined) {
  const resolved = Number(value ?? 0)

  if (!Number.isFinite(resolved) || resolved <= 0) {
    return null
  }

  return resolved
}

export function kokoInstallmentAmount(price: number | string | null | undefined, percentage: number | string | null | undefined = 0) {
  const resolvedPrice = positiveNumber(price)

  if (resolvedPrice === null) {
    return null
  }

  const resolvedPercentage = Math.max(0, Number(percentage ?? 0))
  const safePercentage = Number.isFinite(resolvedPercentage) ? resolvedPercentage : 0
  const payable = resolvedPrice + ((resolvedPrice * safePercentage) / 100)

  return Math.floor((payable / 3) * 100) / 100
}

export function resolveKokoInstallmentPrice(product: KokoPayProduct | null | undefined) {
  if (!product) {
    return null
  }

  const directAmount = positiveNumber(product.koko_installment_price)

  if (directAmount !== null) {
    return directAmount
  }

  if (!Object.prototype.hasOwnProperty.call(product, 'koko_pay_percentage')) {
    return null
  }

  return kokoInstallmentAmount(product.display_price, product.koko_pay_percentage)
}

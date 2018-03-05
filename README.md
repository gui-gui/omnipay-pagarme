# Omnipay: Pagar.Me

**Pagar.Me gateway for the Omnipay PHP payment processing library**

WIP - changes to make api v2017-08-28 work.

Forking since there are some breaking changes between this api version and the previous one. A merge between this fork `descubraomundo/omnipay-pagarme` is not a good idea.

TODO

- Support card Reference for 1 click buy
- For commerce plugin wrapper
  - hook into commerce_modifyItemBag to modify items to have proper format
  - hook into commerce_modifyPaymentRequest to modift amount to cents and maybe add postback url
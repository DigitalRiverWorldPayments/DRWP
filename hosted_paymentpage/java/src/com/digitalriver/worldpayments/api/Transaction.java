package com.digitalriver.worldpayments.api;

import com.digitalriver.worldpayments.api.utils.Parameter;

public class Transaction {

    @Parameter(shortName = "C")
    Long id;

    public Long getId() {
        return id;
    }
}


<style>
    .auth-wrap {
        min-height: calc(100vh - 80px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 16px;
    }

    .auth-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 8px 40px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 420px;
        overflow: hidden;
    }

    .auth-card-head {
        background: #be0000;
        padding: 32px 36px 28px;
        text-align: center;
    }

    .auth-card-head h2 {
        color: #fff;
        font-size: 1.3rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        margin: 0 0 4px;
    }

    .auth-card-head p {
        color: rgba(255, 255, 255, 0.72);
        font-size: 0.78rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin: 0;
    }

    .auth-card-body {
        padding: 32px 36px 36px;
    }

    .alert-box {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: 12px 14px;
        border-radius: 8px;
        font-size: 0.84rem;
        margin-bottom: 22px;
    }

    .alert-error {
        background: #fff0f0;
        border: 1px solid #fcc;
        color: #8b0000;
    }

    .alert-success {
        background: #f0fff4;
        border: 1px solid #b7ebc6;
        color: #166534;
    }

    .f-group {
        margin-bottom: 18px;
    }

    .f-group label {
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: #555;
        margin-bottom: 6px;
    }

    .f-group input {
        width: 100%;
        padding: 11px 14px;
        border: 1.5px solid #e0e0e0;
        border-radius: 8px;
        font-size: 0.95rem;
        color: #111;
        background: #fafafa;
        outline: none;
        font-family: 'Montserrat', sans-serif;
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .f-group input:focus {
        border-color: #be0000;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(190, 0, 0, 0.1);
    }

    .f-group input.is-err {
        border-color: #be0000;
        background: #fff8f8;
    }

    .auth-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 26px;
    }

    .remember-row {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .remember-row input[type='checkbox'] {
        accent-color: #be0000;
        width: 15px;
        height: 15px;
        cursor: pointer;
    }

    .remember-row label {
        font-size: 0.85rem;
        color: #666;
        cursor: pointer;
        font-weight: 400;
        text-transform: none;
        letter-spacing: 0;
        margin: 0;
    }

    .auth-link {
        color: #be0000;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
    }

    .auth-link:hover {
        color: #9a0000;
        text-decoration: underline;
    }

    .btn-primary {
        width: 100%;
        padding: 13px;
        background: #be0000;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 0.93rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        cursor: pointer;
        font-family: 'Montserrat', sans-serif;
        box-shadow: 0 4px 16px rgba(190, 0, 0, 0.28);
        transition: background 0.15s, box-shadow 0.15s, transform 0.1s;
    }

    .btn-primary:hover {
        background: #9a0000;
        box-shadow: 0 6px 20px rgba(190, 0, 0, 0.38);
    }

    .btn-primary:active {
        transform: scale(0.98);
    }

    .auth-footnote {
        margin-top: 24px;
        font-size: 0.73rem;
        color: #777;
        text-align: center;
        line-height: 1.8;
    }

    .auth-caption {
        margin: -2px 0 22px;
        color: #666;
        font-size: 0.86rem;
        line-height: 1.6;
    }
</style>

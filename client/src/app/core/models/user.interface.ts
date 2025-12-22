export interface UserSignUp {
    full_name: string;
    email: string;
    password: string;
}

export interface UserLogin {
    email: string;
    password: string;
}

export interface SignUpResponse {
    success:boolean;
    message:string;
}

export interface LoginResponse {
    success:boolean;
    message:string;
        data?: {
        user: User;
    };
}
export interface User  {
        id:number;
        name: string;
        email: string;
        role: string;
    }
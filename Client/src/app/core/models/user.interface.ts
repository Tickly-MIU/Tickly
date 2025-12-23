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

    export interface UserStatistics {
        totalTasks: number;
        completedTasks: number;
        createdAt: Date;
        userId: number;
        statId: number;
    }
    export interface category{
        user_id:number;
        category_id:number;
        category_name:string;
    }

    export interface profile{
        id:number;
        name?: string;
        full_name?: string;
        email: string;
        role: string;
        created_at:Date;
    }
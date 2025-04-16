export interface Player {
    id: string
    user_id: string
    name: string
    rating: number
    country_code: string
    created_at: Date
    updated_at: Date
}

export interface Rating {
    id: string
    rating: number
    rateable_id: string
    rateable_type: string
    created_at: Date
    updated_at: Date
}

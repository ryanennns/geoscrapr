export interface Player {
    id: string
    user_id: string
    name: string
    rating: number
    country_code: string
    created_at: string
    updated_at: string
}

export interface Team {
    id: string
    team_id: string
    name: string
    rating: number
    player_a: Player
    player_b: Player
    created_at: string
    updated_at: string
}

export const EMPTY_PLAYER: Player = {
    id: '',
    user_id: '',
    name: '',
    rating: 0,
    country_code: '',
    created_at: '2000-01-01',
    updated_at: '2000-01-01',
}

export interface Rating {
    id: string
    rating: number
    rateable_id: string
    rateable_type: string
    created_at: string
    updated_at: string
}

export interface Snapshot {
    date: string;
    buckets: {
        [range: string]: number;
    };
    n: number;
}

export const isTeam = (maybeTeam: any): maybeTeam is Team => maybeTeam.player_a && maybeTeam.player_b

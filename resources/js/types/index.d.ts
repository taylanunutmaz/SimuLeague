export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export enum TournamentStatus {
    NotStarted = 'not_started',
    InProgress = 'in_progress',
    Finished = 'finished',
}

export interface Standing {
    id: number;
    played: number;
    won: number;
    drawn: number;
    lost: number;
    goals_for: number;
    goals_against: number;
    goal_difference: number;
    points: number;
    team?: Team;
}

export interface TheMatch {
    id: number;
    week: number;
    home_score: number;
    away_score: number;
    home_team?: Team;
    away_team?: Team;
    is_played: boolean;
}

interface PredictedChampionshipRates {
    team: Team;
    strength_rating: number;
    championship_probability: number;
}

export interface Tournament {
    id: number;
    name: string;
    status: TournamentStatus;
    number_of_weeks: number;
    last_played_week: number;
    teams?: Team[];
    matches?: TheMatch[];
    created_at: string;
}

export interface Team {
    id: number;
    name: string;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginationMeta {
    current_page: number;
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
    links: Array<PaginationLink>;
}

export type Resource<T> = {
    data: T;
};

export type PaginatedResource<T> = {
    data: T[];
    links: {
        first: string;
        last: string;
        prev: string | null;
        next: string | null;
    };
    meta: PaginationMeta;
};

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};

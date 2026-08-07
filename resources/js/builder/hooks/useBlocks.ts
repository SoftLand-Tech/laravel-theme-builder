import { useCallback, useReducer } from 'react';
import { arrayMove } from '@dnd-kit/sortable';
import { defaultPropsFor } from '../config/blocks';
import { hydrateBlocks, makeBlockId, type Block, type BlockProps } from '../types/blocks';

type State = Block[];

type Action =
    | { kind: 'add'; id: string; blockType: string; atIndex?: number }
    | { kind: 'remove'; id: string }
    | { kind: 'duplicate'; id: string; newId: string }
    | { kind: 'move'; id: string; dir: -1 | 1 }
    | { kind: 'reorder'; activeId: string; overId: string }
    | { kind: 'reorderAt'; from: number; to: number }
    | { kind: 'update'; id: string; partial: BlockProps }
    | { kind: 'toggleHide'; id: string };

function reducer(state: State, action: Action): State {
    switch (action.kind) {
        case 'add': {
            const block: Block = {
                id: action.id,
                type: action.blockType,
                props: defaultPropsFor(action.blockType),
            };
            if (action.atIndex === undefined) return [...state, block];
            const next = [...state];
            next.splice(action.atIndex, 0, block);
            return next;
        }
        case 'remove':
            return state.filter((b) => b.id !== action.id);
        case 'duplicate': {
            const idx = state.findIndex((b) => b.id === action.id);
            if (idx === -1) return state;
            const copy: Block = {
                id: action.newId,
                type: state[idx].type,
                props: structuredClone(state[idx].props),
            };
            const next = [...state];
            next.splice(idx + 1, 0, copy);
            return next;
        }
        case 'move': {
            const idx = state.findIndex((b) => b.id === action.id);
            const target = idx + action.dir;
            if (idx === -1 || target < 0 || target >= state.length) return state;
            return arrayMove(state, idx, target);
        }
        case 'reorder': {
            const oldIndex = state.findIndex((b) => b.id === action.activeId);
            const newIndex = state.findIndex((b) => b.id === action.overId);
            if (oldIndex === -1 || newIndex === -1) return state;
            return arrayMove(state, oldIndex, newIndex);
        }
        case 'reorderAt': {
            const { from, to } = action;
            if (from < 0 || from >= state.length) return state;
            const clamped = Math.max(0, Math.min(to, state.length - 1));
            if (from === clamped) return state;
            return arrayMove(state, from, clamped);
        }
        case 'update':
            return state.map((b) =>
                b.id === action.id ? { ...b, props: { ...b.props, ...action.partial } } : b,
            );
        case 'toggleHide':
            return state.map((b) =>
                b.id === action.id ? { ...b, props: { ...b.props, hidden: ! (b.props.hidden ?? false) } } : b,
            );
        default:
            return state;
    }
}

export interface UseBlocks {
    blocks: Block[];
    addBlock: (blockType: string, atIndex?: number) => string;
    removeBlock: (id: string) => void;
    duplicateBlock: (id: string) => string | undefined;
    moveBlock: (id: string, dir: -1 | 1) => void;
    reorder: (activeId: string, overId: string) => void;
    reorderAt: (from: number, to: number) => void;
    updateProps: (id: string, partial: BlockProps) => void;
    toggleHide: (id: string) => void;
}

/**
 * Owns the editable block tree. Incoming wire blocks are hydrated with client
 * ids; every mutation is immutable so React + the autosave snapshot ref stay in
 * sync. `addBlock`/`duplicateBlock` return the new block id so the caller can
 * select it.
 */
export function useBlocks(initial: Block[] = []): UseBlocks {
    const [blocks, dispatch] = useReducer(reducer, initial, (init) => hydrateBlocks(init));

    const addBlock = useCallback((blockType: string, atIndex?: number) => {
        const id = makeBlockId(blockType);
        dispatch({ kind: 'add', id, blockType, atIndex });
        return id;
    }, []);

    const removeBlock = useCallback((id: string) => dispatch({ kind: 'remove', id }), []);
    const duplicateBlock = useCallback((id: string) => {
        const newId = makeBlockId('dup');
        dispatch({ kind: 'duplicate', id, newId });
        return newId;
    }, []);
    const moveBlock = useCallback((id: string, dir: -1 | 1) => dispatch({ kind: 'move', id, dir }), []);
    const reorder = useCallback((activeId: string, overId: string) => dispatch({ kind: 'reorder', activeId, overId }), []);
    const reorderAt = useCallback((from: number, to: number) => dispatch({ kind: 'reorderAt', from, to }), []);
    const updateProps = useCallback((id: string, partial: BlockProps) => dispatch({ kind: 'update', id, partial }), []);
    const toggleHide = useCallback((id: string) => dispatch({ kind: 'toggleHide', id }), []);

    return { blocks, addBlock, removeBlock, duplicateBlock, moveBlock, reorder, reorderAt, updateProps, toggleHide };
}
